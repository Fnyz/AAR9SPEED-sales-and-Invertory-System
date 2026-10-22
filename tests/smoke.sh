#!/usr/bin/env bash
# Smoke test: log in and load every page, failing on PHP errors or bad status codes.
#
# Usage: tests/smoke.sh <base-url> <username> <password>
# Example: tests/smoke.sh http://127.0.0.1:8000 admin secret

set -uo pipefail

BASE="${1:?base URL required}"
USER="${2:?username required}"
PASS="${3:?password required}"

JAR="$(mktemp)"
BODY="$(mktemp)"
trap 'rm -f "$JAR" "$BODY"' EXIT

ERRORS='Fatal error|Parse error|Uncaught|Warning:|Failed opening required'
failures=0

check() {
    local label="$1" status="$2"
    if [[ "$status" != "200" ]]; then
        echo "FAIL $label: HTTP $status"
        failures=$((failures + 1))
    elif grep -qE "$ERRORS" "$BODY"; then
        echo "FAIL $label: PHP error in output"
        grep -oE "($ERRORS)[^<]{0,200}" "$BODY" | head -3 | sed 's/^/     /'
        failures=$((failures + 1))
    else
        echo "ok   $label"
    fi
}

# Pages must refuse visitors who are not logged in.
for page in Dashboard.php Sale.php Product.php pendingProduct.php InactiveProduct.php "ProductView.php?code=202301" \
            "transaction.php?code=30118222945" "print_order.php?id=1" Customer.php; do
    location=$(curl -s -o /dev/null -w '%{redirect_url}' "$BASE/$page")
    if [[ "$location" == *"login.php" ]]; then
        echo "ok   $page requires login"
    else
        echo "FAIL $page should redirect logged-out visitors to login.php (got: ${location:-no redirect})"
        failures=$((failures + 1))
    fi
done

# Log in: read the CSRF token from the form, then post the credentials.
status=$(curl -s -o "$BODY" -w '%{http_code}' -c "$JAR" -b "$JAR" "$BASE/login.php")
check "login.php" "$status"
token=$(grep -oE 'name="csrf_token" value="[^"]+"' "$BODY" | head -1 | sed -E 's/.*value="([^"]+)"/\1/')
if [[ -z "$token" ]]; then
    echo "FAIL could not find CSRF token on login page"
    exit 1
fi

location=$(curl -s -o /dev/null -w '%{redirect_url}' -c "$JAR" -b "$JAR" \
    --data-urlencode "csrf_token=$token" --data-urlencode "username=$USER" --data-urlencode "password=$PASS" \
    "$BASE/login.php")
if [[ "$location" != *"Dashboard.php" ]]; then
    echo "FAIL login did not redirect to Dashboard.php (got: ${location:-no redirect})"
    exit 1
fi
echo "ok   login redirect"

# Admin-only pages must send you to the admin password page first.
for pair in Staff.php:SecurityCheck.php Order.php:Securitylogin.php; do
    page="${pair%%:*}" gate="${pair##*:}"
    location=$(curl -s -o /dev/null -w '%{redirect_url}' -b "$JAR" "$BASE/$page")
    if [[ "$location" == *"$gate" ]]; then
        echo "ok   $page redirects to $gate before the admin check"
    else
        echo "FAIL $page should redirect to $gate before the admin check (got: ${location:-no redirect})"
        failures=$((failures + 1))
    fi
done

# Pass the admin password page (the seeded admin uses the same password).
status=$(curl -s -o "$BODY" -w '%{http_code}' -b "$JAR" "$BASE/SecurityCheck.php")
check "SecurityCheck.php" "$status"
token=$(grep -oE 'name="csrf_token" value="[^"]+"' "$BODY" | head -1 | sed -E 's/.*value="([^"]+)"/\1/')
location=$(curl -s -o /dev/null -w '%{redirect_url}' -b "$JAR" --data-urlencode "csrf_token=$token" \
    --data-urlencode "password=$PASS" "$BASE/SecurityCheck.php")
if [[ "$location" == *"Staff.php" ]]; then
    echo "ok   admin check passed"
else
    echo "FAIL admin check did not redirect to Staff.php (got: ${location:-no redirect})"
    failures=$((failures + 1))
fi

for page in Dashboard.php Sale.php Customer.php Product.php Category.php Supplier.php Staff.php Order.php \
            invoice.php Refund.php pendingProduct.php InactiveProduct.php \
            "ProductView.php?code=202301" "transaction.php?code=30118222945" "print_order.php?id=1" \
            "Refund.php?code=30118222945" "Order.php?item=1&mode=restock" \
            Reports.php "Reports.php?group=product" "Reports.php?group=staff" Logs.php "Logs.php?type=signin"; do
    status=$(curl -s -o "$BODY" -w '%{http_code}' -b "$JAR" "$BASE/$page")
    check "$page" "$status"
done

# Sales report CSV download.
status=$(curl -s -o "$BODY" -w '%{http_code}' -b "$JAR" "$BASE/Reports.php?export=csv&from=2023-01-01&to=2030-12-31")
check "Reports.php CSV" "$status"
if ! grep -q 'Invoices,Units,Revenue' "$BODY"; then
    echo "FAIL Reports.php CSV: missing header row"
    failures=$((failures + 1))
fi

# The notification bell poll (AJAX) needs the session's CSRF token.
status=$(curl -s -o "$BODY" -w '%{http_code}' -b "$JAR" "$BASE/Dashboard.php")
token=$(grep -oE 'name="csrf-token" content="[^"]+"' "$BODY" | head -1 | sed -E 's/.*content="([^"]+)"/\1/')
status=$(curl -s -o "$BODY" -w '%{http_code}' -b "$JAR" --data-urlencode "csrf_token=$token" --data "view=" \
    "$BASE/ajax/notification.php")
check "ajax/notification.php" "$status"
if ! grep -q '"unseen"' "$BODY"; then
    echo "FAIL ajax/notification.php: response is not the expected JSON"
    failures=$((failures + 1))
fi

echo
if [[ $failures -gt 0 ]]; then
    echo "$failures check(s) failed"
    exit 1
fi
echo "All smoke checks passed"
