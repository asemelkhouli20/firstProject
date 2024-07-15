<?php

test('test_offices_get_rout_api', function () {
    $response = $this->get('/api/offices');
    $response->assertOk()->dump();
});
