<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Helpers\CreatesAccounts;
use Tests\Helpers\CreatesCategories;
use Tests\Helpers\CreatesTransactions;
use Tests\Helpers\CreatesTransfers;
use Tests\Helpers\CreatesUsers;

abstract class TestCase extends BaseTestCase
{
    use CreatesAccounts;
    use CreatesCategories;
    use CreatesTransactions;
    use CreatesTransfers;
    use CreatesUsers;
}
