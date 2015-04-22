<p class="lead">Dear <?= show_safely($this->to->full_name()) ?>,</p>
<p>This message has been sent to you by <?= show_safely(current_user()->full_name()) ?>.</p>
<hr>
<p><?= nl2br(show_safely($this->message->Content)) ?></p>
<hr>
<p><?= show_safely(current_user()->email) ?></p>
