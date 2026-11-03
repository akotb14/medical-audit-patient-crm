<?php

it('redirects guests from the root path to the login page', function () {
    $this->get('/')->assertRedirect(route('auth.login'));
});

it('sends signed-in administrators from the root path to the dashboard', function () {
    actingAsAdmin();

    $this->get('/')->assertRedirect(route('dashboard.index'));
});

it('sends signed-in receptionists from the root path to the clinics list', function () {
    actingAsReceptionist();

    $this->get('/')->assertRedirect(route('clinics.index'));
});
