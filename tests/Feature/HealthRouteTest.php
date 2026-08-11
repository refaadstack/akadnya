<?php

test('health endpoint reports ok with a reachable database', function () {
    $response = $this->get('/health');

    $response->assertOk();
    $response->assertJson([
        'status' => 'ok',
        'database' => 'ok',
    ]);
});