<?php

return [
    'enabled' => (bool) env('SMART_ORDER_ASSISTANT_ENABLED', true),
    'max_input_chars' => (int) env('SMART_ORDER_ASSISTANT_MAX_INPUT_CHARS', 700),
    'catalog_limit' => (int) env('SMART_ORDER_ASSISTANT_CATALOG_LIMIT', 200),
];
