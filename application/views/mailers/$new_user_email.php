<h1>New User<small> <?= show_safely($this->user->full_name()) ?></small></h1>
<p>A new user has registered and requires verification.</p>
<p>Please <a href="<?= address('users', 'list', '', array('status' => 'unverified')) ?>">login</a> to confirm their details and activate their account.</p>
