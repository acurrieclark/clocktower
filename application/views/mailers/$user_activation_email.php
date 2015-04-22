<h1><?php echo show_safely($this->user->full_name()) ?><small> Confirmation Email</small></h1>

<p class="lead">Before we can process your registration, please confirm your email address using the link below.</p>

<p>Your confirmation link is: <a href="<?= ABSOLUTE . 'users/confirm/' . $this->activation_code ?>"><?php echo ABSOLUTE . 'users/confirm/' . $this->activation_code ?></a></p>

