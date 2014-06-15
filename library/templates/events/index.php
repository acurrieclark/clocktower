
<?php set('header-title', 'Events') ?>


<?php messages(); ?>

<div class="row-fluid">

	<div class="span8">

	<?php if ($this->events): ?>

			<div class="event-items">

				<?php $count = 1; ?>

				<?php foreach ($this->events as $event): ?>

					<?php
					?>


						<?php if ($count == 1): ?>
						<?php set('title', limit_title_length('Upcoming Event: '.$event->Title));	?>
							<!-- Hero Unit -->

							<div class="hero-unit">
								<?= show_header_image($event); ?>
							     <h1><?= link_to($event->Title, 'events', $event->id) ?></h1>
								 		<div class="info">
								 			 <?= show_category($event) ?>
								 			 <?= show_start_date($event) ?>
								 		</div>
							            <p><?= show_description($event) ?></p>
							          </div>

						<?php elseif ($count >1 && $count < 4): ?>

							<!-- Second row -->

							<?php if ($count == 2)	echo "<div class='row-fluid'>";?>

								<div class="middle-item span6">
										<?= show_header_image($event, array('class' => 'img-polaroid')); ?>
									<h2><?= link_to($event->Title, 'events', $event->id->value) ?></h2>
							 		<div class="info">
										<?= show_category($event) ?>
							 			<?= show_start_date($event) ?>
									</div>
									<p><?= show_description($event) ?></p>
								</div>

							<?php if ($count == 3 || (sizeof($this->events) == "2")) echo '</div>'; ?>


						<?php else: ?>

							<!-- Third Row -->

								<div class="lower-headline">
									<h4><?= link_to($event->Title, 'events', $event->id->value) ?></h4>
		 				 			<div class="info-inline">
										<?= show_category($event) ?>
										<?= show_start_date($event) ?>
									</div>
								</div>

						<?php endif ?>

						<?php $count++; ?>

				<?php endforeach ?>


					</div>




		<?php else: ?>

			<div class="page-header">
				<h1>event could not be found</h1>

			</div>

			<p class="lead">We do not seem to be able to find the event you are looking for. Please ensure that you have typed the URL correctly in your address bar.</p>


		<?php endif ?>

	</div>

	<div class="span4">

		<?php render_partial('latest_news') ?>

	</div>

</div>
