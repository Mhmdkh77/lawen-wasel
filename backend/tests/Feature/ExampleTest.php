<?php

test('the login entry point redirects to the admin login page', function () {
    $this->get('/login')->assertRedirect(route('admin.login'));
});
