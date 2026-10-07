<?php
require __DIR__.'/../lib/auth_admin.php';$_SESSION=[];session_destroy();redirect('/admin/');
