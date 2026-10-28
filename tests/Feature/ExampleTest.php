<?php

test('the login page renders for guests', function () {
    $this->get(route('auth.login'))->assertOk();
});
