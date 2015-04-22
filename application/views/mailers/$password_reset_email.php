<h1>Password Reset <small><?= show_safely($this->user->full_name()) ?></small></h1>
<p>You have requested to reset your password. Simply click the button below choose a new password.</p>
<p>If you were not expecting this email, please don't worry. Your password has not been reset. Simply login as usual and continue to use the site.</p>
<p><strong>We will never request your <em>current</em> password via email. Please contact the administrator if you have any concerns.</strong></p>

<a href="<?php echo ABSOLUTE . 'users'. DS . 'new-password' . DS . $this->reset_code ?>" class="btn btn-default">Choose a new password.</a>
