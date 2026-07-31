<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

use Tests\Helpers\CreatesUsers;
use Tests\Helpers\CreatesAccounts;
use Tests\Helpers\CreatesCategories;
use Tests\Helpers\CreatesTransactions;
use Tests\Helpers\CreatesTransfers;

abstract class TestCase extends BaseTestCase
{
    use CreatesUsers;
    use CreatesAccounts;
    use CreatesCategories;
    use CreatesTransactions;
    use CreatesTransfers;
}