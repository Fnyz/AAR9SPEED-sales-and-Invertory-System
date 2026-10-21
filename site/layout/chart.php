<?php
// Daily sales line chart as inline SVG, styled by .sales-chart in styles/tabler-theme.css.

function chart_peso_short(float $value): string
{
    if ($value >= 1000) {
        $k = $value / 1000;
        return '₱' . (floor($k) == $k ? number_format($k, 0) : number_format($k, 1)) . 'k';
    }
    return '₱' . number_format($value, 0);
}

/** Round up to 1, 2, 2.5 or 5 × a power of ten, so the gridlines land on clean values. */
function chart_nice_max(float $max): float
{
    if ($max <= 0) {
        return 1000;
    }
    $magnitude = 10 ** floor(log10($max));
    foreach ([1, 2, 2.5, 5, 10] as $step) {
        if ($max <= $step * $magnitude) {
            return $step * $magnitude;
        }
    }
    return 10 * $magnitude;
}

/**
 * @param array $rows  [['day' => 'Y-m-d', 'total' => float], …] oldest first, today last
 */
function render_sales_chart(array $rows): string
{
    $left = 48; $right = 590; $top = 16; $bottom = 190;
    $count = count($rows);
    $yMax = chart_nice_max(max(array_column($rows, 'total') ?: [0]));

    $points = [];
    foreach ($rows as $i => $row) {
        $x = $count > 1 ? $left + $i * ($right - $left) / ($count - 1) : $left;
        $y = $bottom - $row['total'] / $yMax * ($bottom - $top);
        $points[] = [round($x, 1), round($y, 1)];
    }
    $line = implode(' ', array_map(fn($p) => $p[0] . ',' . $p[1], $points));
    $area = "$left,$bottom $line $right,$bottom";
    [$lastX, $lastY] = end($points);

    // Seven evenly spread date labels; the last one is always today.
    $labelIdx = $count <= 7 ? range(0, $count - 1) : array_unique(array_map(fn($n) => (int)round($n * ($count - 1) / 6), range(0, 6)));
    $labels = '';
    foreach ($labelIdx as $n => $idx) {
        $isLast = $idx === $count - 1;
        $text = $isLast ? 'Today' : date($count <= 7 ? 'D' : 'j M', strtotime($rows[$idx]['day']));
        $anchor = $isLast ? 'end' : ($idx === 0 && $count > 7 ? 'start' : 'middle');
        $labels .= sprintf('<text x="%s" y="210" text-anchor="%s">%s</text>', $points[$idx][0], $anchor, htmlspecialchars($text));
    }

    $mid = $bottom - ($bottom - $top) / 2;
    $total = array_sum(array_column($rows, 'total'));
    $aria = sprintf('Daily sales for the last %d days, %s in total. Today: %s.', $count, '₱' . number_format($total, 2), '₱' . number_format(end($rows)['total'], 2));

    return sprintf(
        '<svg class="sales-chart" viewBox="0 0 600 220" role="img" aria-label="%s">'
        . '<line class="grid" x1="%d" y1="%d" x2="%d" y2="%d"></line>'
        . '<line class="grid-dash" x1="%d" y1="%s" x2="%d" y2="%s"></line>'
        . '<line class="grid-dash" x1="%d" y1="%d" x2="%d" y2="%d"></line>'
        . '<text x="%d" y="%d" text-anchor="end">₱0</text>'
        . '<text x="%d" y="%s" text-anchor="end">%s</text>'
        . '<text x="%d" y="%d" text-anchor="end">%s</text>'
        . '<polygon class="area" points="%s"></polygon>'
        . '<polyline class="line" points="%s"></polyline>'
        . '<circle class="dot" cx="%s" cy="%s" r="5"></circle>%s</svg>',
        htmlspecialchars($aria),
        $left, $bottom, $right, $bottom,
        $left, $mid, $right, $mid,
        $left, $top, $right, $top,
        $left - 8, $bottom + 4,
        $left - 8, $mid + 4, chart_peso_short($yMax / 2),
        $left - 8, $top + 4, chart_peso_short($yMax),
        $area, $line, $lastX, $lastY, $labels
    );
}
