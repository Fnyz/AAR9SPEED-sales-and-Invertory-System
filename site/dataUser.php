 <?php
    if (isset($_SESSION['getId'])) {
        $dash->id = $_SESSION['getId'];
        $res = $dash->getStafftoUpdate();
    }

    ?>

 <!-- Modal -->
 <div class=" modal fade" id="UpdateAdmin" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
     <div class="modal-dialog">
         <div class="modal-content">
             <div class="modal-header">
                 <h1 class="modal-title fs-5" id="staticBackdropLabel">Update Account</h1>

                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
             </div>

             <form action="ajax/updatingUser.php" method="post">
                 <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                 <div class="modal-body">

                     <?php foreach ($res as $val) : extract($val) ?>
                         <div class="mb-3">
                             <label for="">USERNAME:</label>
                             <input type="hidden" class="form-control" placeholder="Username" value="<?php echo $STAFF_ID ?>" name="id">
                             <input type="text" class="form-control" placeholder="Username" value="<?php echo $STAFF_USER ?>" name="user" require>
                         </div>
                         <div class="mb-3">
                             <input type="password" class="form-control" placeholder="PASSWORD" value="" name="pass" require>
                        </div>
                         <div class="mb-3">
                             <label for="">FIRST NAME:</label>
                             <input type="text" class="form-control" placeholder="First Name" value="<?php echo $STAFF_FNAME ?>" name="fname" require>
                         </div>
                         <div class="mb-3">
                             <label for="">LAST NAME:</label>
                             <input type="text" class="form-control" placeholder="Lastname" value="<?php echo $STAFF_LNAME ?>" name="lname" require>
                         </div>
                         <div class="mb-3">
                             <label for="">PHONE-NUMBER:</label>
                             <input type="number" class="form-control" placeholder="Phone number" value="<?php echo $STAFF_PHONENUM ?>" name="phone" require>
                         </div>
                         <div class="mb-3">
                             <input type="email" class="form-control" placeholder="Email" value="<?php echo $STAFF_EMAIL ?>" name="email" require>
                         </div>
                         <div class="mb-3">
                             <label for="">LOCATION:</label>
                             <input type="text" class="form-control" placeholder="Address" value="<?php echo $STAFF_ADDRESS ?>" name="add" require>
                         </div>

                         <div class="mb-3">
                             <label for="">Date: </label>
                             <input type="text" class="form-control" placeholder="Address" value="<?php echo date('Y-m-d H:i:s') ?>" name="add" disabled>
                         </div>

                     <?php endforeach; ?>

                 </div>
                 <div class="modal-footer">
                     <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                     <button type="submit" class="btn btn-primary">Save</button>
                 </div>
             </form>

         </div>
     </div>
 </div>