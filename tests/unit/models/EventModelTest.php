<?php

use App\Models\Event;
use Carbon\Carbon;

class EventModelTest extends TestCase
{
    private Carbon $testTodayDate;
    private Event $testEvent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testTodayDate = $this->getTestBaseDate();
        Carbon::setTestNow($this->testTodayDate);

        $this->testEvent = new Event();
    }

    /**
     * @dataProvider getDataForTestGetHasStartedAttribute
     */
    public function testGetHasStartedAttribute(Carbon $startDate, bool $expected)
    {
        $this->testEvent->dateStart = $startDate;

        $this->assertEquals($expected, $this->testEvent->has_started);
    }

    /**
     * @dataProvider getDataForTestGetHasEndedAttribute
     */
    public function testGetHasEndedAttribute(Carbon $endDate, bool $expected)
    {
        $this->testEvent->dateEnd = $endDate;

        $this->assertEquals($expected, $this->testEvent->has_ended);
    }

    /**
     * @dataProvider getDataForTestGetCanConfirmSlotsAttribute
     */
    public function testGetCanConfirmSlotsAttribute(Carbon $startDate, bool $expected)
    {
        $this->testEvent->dateStart = $startDate;

        $this->assertEquals($expected, $this->testEvent->can_confirm_slots);
    }

    public function getDataForTestGetHasStartedAttribute()
    {
        return [
            [$this->getTestBaseDate()->copy()->addDays(3), false],
            [$this->getTestBaseDate()->copy()->addMinute(), false],
            [$this->getTestBaseDate()->copy()->subDays(10), true],
            [$this->getTestBaseDate()->copy()->subSeconds(45), true]
        ];
    }

    public function getDataForTestGetHasEndedAttribute()
    {
        return [
            [$this->getTestBaseDate()->copy()->addDays(3), false],
            [$this->getTestBaseDate()->copy()->addMinute(), false],
            [$this->getTestBaseDate()->copy()->subDays(10), true],
            [$this->getTestBaseDate()->copy()->subSeconds(45), true]
        ];
    }

    public function getDataForTestGetCanConfirmSlotsAttribute()
    {
        $minConfirmDays = Event::FLIGHT_CONFIRM_MAX_DAYS_BEFORE;

        return [
            [$this->getTestBaseDate()->copy()->addDays($minConfirmDays * 2), false],
            [$this->getTestBaseDate()->copy()->addMinute(), true],
            [$this->getTestBaseDate()->copy()->subMonth(), false],
            [$this->getTestBaseDate()->copy()->addDays($minConfirmDays), true]
        ];
    }

    private function getTestBaseDate()
    {
        return Carbon::createFromDate(2022, 1, 6);
    }
}
