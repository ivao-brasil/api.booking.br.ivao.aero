<?php

use App\Models\Event;
use App\Models\User;
use App\Policies\EventDataExportPolicy;
use Illuminate\Support\Carbon;

class EventDataExportPolicyTest extends TestCase
{
    private EventDataExportPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new EventDataExportPolicy();
    }

    public function testCreateClass()
    {
        $this->assertInstanceOf(EventDataExportPolicy::class, $this->policy);
    }

    public function testDeniesRequestWhenTheUserIsNotAdmin()
    {
        $testUser = new User();
        $testEvent = new Event();

        $result = $this->policy->export($testUser, $testEvent);

        $this->assertTrue($result->denied());
    }

    public function testDeniesRequestWhenTheEventIsNotFinished()
    {
        $testUser = new User();
        $testUser->admin = true;
        $testEvent = $this->prepateTestEvent(false);

        $result = $this->policy->export($testUser, $testEvent);

        $this->assertTrue($result->denied());
    }

    public function testAllowsRequest()
    {
        $testUser = new User();
        $testUser->admin = true;
        $testEvent = $this->prepateTestEvent();

        $result = $this->policy->export($testUser, $testEvent);

        $this->assertFalse($result->denied());
    }

    private function prepateTestEvent(bool $hasEnded = true): Event
    {
        $testStartDate = Carbon::now();
        $testEvent = new Event([
            'dateStart' => $testStartDate,
            'dateEnd' => $testStartDate->addDays($hasEnded ? -7 : 7)
        ]);

        return $testEvent;
    }
}
