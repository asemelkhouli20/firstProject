<?php

test('test_tags_get_rout_api', function () {
    $response = $this->get('/api/tags');
    $response->assertOk();
});
