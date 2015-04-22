
		<?php

		if (!isset($variables['active'])) $variables['active'] = '';

		if ($variables['active'] == 'new') {
				$disabling_class = ' class="disabled"';

				javascript_start();

				 ?>

				$(function () {
					$('.disabled').bind('click', false);
				});

				 <?php

				javascript_end();


			}
		else $disabling_class = '';
				?>



		<ul class="nav nav-stacked nav-pills">

			<?php if (is_admin()): ?>
				<h3>Admin Functions</h3>
				<li<?php if ($variables['active'] == 'new') echo ' class="active" '?>><?= link_to(icon('pencil')." New User", "users", "new", $this->user->id) ?></li>
				<li<?php if ($variables['active'] == 'edit') echo ' class="active" '; echo $disabling_class;?>><?= link_to(icon('edit')." Edit Profile", "users", "edit", $this->user->id) ?></li>
				<li<?php if ($variables['active'] == 'password') echo ' class="active" '; echo $disabling_class;?>><?= link_to(icon('lock')." Change Password", "users", "change_password", $this->user->id) ?></li>
			<?php else: ?>
				<h3>Your Profile</h3>
				<li<?php if ($variables['active'] == 'profile') echo ' class="active" '?>><?= link_to(icon("user")." View", "users", "show", current_user()->id) ?></li>
				<li<?php if ($variables['active'] == 'edit') echo ' class="active" '?>><?= link_to(icon("edit")." Edit", "users", "edit", current_user()->id) ?></li>
				<li<?php if ($variables['active'] == 'password') echo ' class="active" '?>><?= link_to(icon('lock')." Change Password", "users", "change-password", current_user()->id) ?></li>
			<?php endif ?>

			<li class="divider"></li>

			<li<?php if ($variables['active'] == 'list') echo ' class="active" '?>><?= link_to(icon("th-list")." Users List", "users", "list") ?></li>
		</ul>
