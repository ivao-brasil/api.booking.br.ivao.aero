<?php

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class EventModelTest extends TestCase
{
    private Carbon $testTodayDate;
    private Event $testEvent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testTodayDate = $this->getTestBaseDate();
        Carbon::setTestNow($this->testTodayDate);

        Config::set('app.slot.before_event_to_confirm_days', 7);
        Config::set('app.slot.ignore_slot_confirmation_days', 7);

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

    /**
     * @dataProvider getDataForTestGetCanAutoBookAttribute
     */
    public function testGetCanAutoBookAttribute(
        Carbon $startDate,
        int $beforeEventConfirmDays,
        ?int $ignoreSlotConfirmationDays,
        bool $expected
    ) {
        Config::set('app.slot.before_event_to_confirm_days', $beforeEventConfirmDays);
        Config::set('app.slot.ignore_slot_confirmation_days', $ignoreSlotConfirmationDays);

        $this->testEvent->dateStart = $startDate;

        $this->assertEquals($expected, $this->testEvent->can_auto_book);
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
        return [
            [$this->getTestBaseDate()->copy()->addDays(14), false],
            [$this->getTestBaseDate()->copy()->addMinute(), true],
            [$this->getTestBaseDate()->copy()->subMonth(), false],
            [$this->getTestBaseDate()->copy()->addDays(7), true]
        ];
    }

    public function getDataForTestGetCanAutoBookAttribute()
    {
        return [
            [$this->getTestBaseDate()->copy()->addDays(14), 7, 1, false],
            [$this->getTestBaseDate()->copy()->addMinute(), 7, 1, true],
            [$this->getTestBaseDate()->copy()->addMinute(), 7, null, false],
            [$this->getTestBaseDate()->copy()->subMonth(), 7, 5, false],
            [$this->getTestBaseDate()->copy()->addDays(7), 7, 7, true]
        ];
    }

    private function getTestBaseDate()
    {
        return Carbon::createFromDate(2022, 1, 6);
    }
}
