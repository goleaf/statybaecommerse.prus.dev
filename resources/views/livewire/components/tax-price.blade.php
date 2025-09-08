<?php
?>

<span>
    {{ \Illuminate\Support\Number::currency($amount, current_currency(), app()->getLocale()) }}
</span>
