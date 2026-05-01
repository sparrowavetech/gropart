<?php

namespace Botble\LoyaltyPoints\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\LoyaltyPoints\Models\LoyaltyLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoyaltyLevelTest extends BaseTestCase
{
    use RefreshDatabase;

    public function test_can_create_loyalty_level(): void
    {
        $level = LoyaltyLevel::query()->create([
            'name' => 'Bronze',
            'min_points' => 0,
            'earning_rate' => 1.0,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 0,
        ]);

        $this->assertDatabaseHas('loyalty_levels', [
            'name' => 'Bronze',
            'min_points' => 0,
            'earning_rate' => 1.0,
        ]);
    }

    public function test_can_create_multiple_levels(): void
    {
        LoyaltyLevel::query()->create([
            'name' => 'Bronze',
            'min_points' => 0,
            'earning_rate' => 1.0,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 0,
        ]);

        LoyaltyLevel::query()->create([
            'name' => 'Silver',
            'min_points' => 500,
            'earning_rate' => 1.1,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 1,
        ]);

        LoyaltyLevel::query()->create([
            'name' => 'Gold',
            'min_points' => 2000,
            'earning_rate' => 1.25,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 2,
        ]);

        $this->assertCount(3, LoyaltyLevel::all());
    }

    public function test_level_with_benefits(): void
    {
        $level = LoyaltyLevel::query()->create([
            'name' => 'Gold',
            'min_points' => 2000,
            'earning_rate' => 1.25,
            'benefits' => "1.25x Point Earning\nFree Shipping\nExclusive Access",
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 2,
        ]);

        $this->assertStringContainsString('1.25x Point Earning', $level->benefits);
        $this->assertStringContainsString('Free Shipping', $level->benefits);
    }

    public function test_level_status_published(): void
    {
        $level = LoyaltyLevel::query()->create([
            'name' => 'Silver',
            'min_points' => 500,
            'earning_rate' => 1.1,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 1,
        ]);

        $this->assertEquals(BaseStatusEnum::PUBLISHED, $level->status);
    }

    public function test_level_status_draft(): void
    {
        $level = LoyaltyLevel::query()->create([
            'name' => 'Platinum',
            'min_points' => 10000,
            'earning_rate' => 2.0,
            'status' => BaseStatusEnum::DRAFT,
            'order' => 4,
        ]);

        $this->assertEquals(BaseStatusEnum::DRAFT, $level->status);
    }

    public function test_level_is_default(): void
    {
        $defaultLevel = LoyaltyLevel::query()->create([
            'name' => 'Bronze',
            'min_points' => 0,
            'earning_rate' => 1.0,
            'is_default' => true,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 0,
        ]);

        $nonDefaultLevel = LoyaltyLevel::query()->create([
            'name' => 'Silver',
            'min_points' => 500,
            'earning_rate' => 1.1,
            'is_default' => false,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 1,
        ]);

        $this->assertTrue($defaultLevel->is_default);
        $this->assertFalse($nonDefaultLevel->is_default);
    }

    public function test_can_update_level(): void
    {
        $level = LoyaltyLevel::query()->create([
            'name' => 'Silver',
            'min_points' => 500,
            'earning_rate' => 1.1,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 1,
        ]);

        $level->update([
            'min_points' => 600,
            'earning_rate' => 1.15,
        ]);

        $this->assertDatabaseHas('loyalty_levels', [
            'id' => $level->id,
            'min_points' => 600,
            'earning_rate' => 1.15,
        ]);
    }

    public function test_can_delete_level(): void
    {
        $level = LoyaltyLevel::query()->create([
            'name' => 'Test Level',
            'min_points' => 100,
            'earning_rate' => 1.0,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 0,
        ]);

        $levelId = $level->id;
        $level->delete();

        $this->assertDatabaseMissing('loyalty_levels', [
            'id' => $levelId,
        ]);
    }

    public function test_levels_ordered_by_order_column(): void
    {
        LoyaltyLevel::query()->create([
            'name' => 'Gold',
            'min_points' => 2000,
            'earning_rate' => 1.25,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 2,
        ]);

        LoyaltyLevel::query()->create([
            'name' => 'Bronze',
            'min_points' => 0,
            'earning_rate' => 1.0,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 0,
        ]);

        LoyaltyLevel::query()->create([
            'name' => 'Silver',
            'min_points' => 500,
            'earning_rate' => 1.1,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 1,
        ]);

        $levels = LoyaltyLevel::query()->orderBy('order')->get();

        $this->assertEquals('Bronze', $levels->first()->name);
        $this->assertEquals('Gold', $levels->last()->name);
    }

    public function test_levels_ordered_by_min_points(): void
    {
        LoyaltyLevel::query()->create([
            'name' => 'Gold',
            'min_points' => 2000,
            'earning_rate' => 1.25,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 2,
        ]);

        LoyaltyLevel::query()->create([
            'name' => 'Bronze',
            'min_points' => 0,
            'earning_rate' => 1.0,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 0,
        ]);

        LoyaltyLevel::query()->create([
            'name' => 'Silver',
            'min_points' => 500,
            'earning_rate' => 1.1,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 1,
        ]);

        $levels = LoyaltyLevel::query()->orderByDesc('min_points')->get();

        $this->assertEquals('Gold', $levels->first()->name);
        $this->assertEquals('Bronze', $levels->last()->name);
    }

    public function test_can_filter_published_levels(): void
    {
        LoyaltyLevel::query()->create([
            'name' => 'Bronze',
            'min_points' => 0,
            'earning_rate' => 1.0,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 0,
        ]);

        LoyaltyLevel::query()->create([
            'name' => 'Silver',
            'min_points' => 500,
            'earning_rate' => 1.1,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 1,
        ]);

        LoyaltyLevel::query()->create([
            'name' => 'Draft Level',
            'min_points' => 10000,
            'earning_rate' => 2.0,
            'status' => BaseStatusEnum::DRAFT,
            'order' => 99,
        ]);

        $publishedLevels = LoyaltyLevel::query()
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->get();

        $this->assertCount(2, $publishedLevels);
    }

    public function test_level_earning_rate_multiplier(): void
    {
        $bronzeLevel = LoyaltyLevel::query()->create([
            'name' => 'Bronze',
            'min_points' => 0,
            'earning_rate' => 1.0,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 0,
        ]);

        $goldLevel = LoyaltyLevel::query()->create([
            'name' => 'Gold',
            'min_points' => 2000,
            'earning_rate' => 1.5,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 2,
        ]);

        $basePoints = 100;
        $bronzePoints = (int) round($basePoints * $bronzeLevel->earning_rate);
        $goldPoints = (int) round($basePoints * $goldLevel->earning_rate);

        $this->assertEquals(100, $bronzePoints);
        $this->assertEquals(150, $goldPoints);
    }

    public function test_find_level_for_points(): void
    {
        LoyaltyLevel::query()->create([
            'name' => 'Bronze',
            'min_points' => 0,
            'earning_rate' => 1.0,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 0,
        ]);

        LoyaltyLevel::query()->create([
            'name' => 'Silver',
            'min_points' => 500,
            'earning_rate' => 1.1,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 1,
        ]);

        LoyaltyLevel::query()->create([
            'name' => 'Gold',
            'min_points' => 2000,
            'earning_rate' => 1.25,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 2,
        ]);

        $pointsToTest = 750;
        $level = LoyaltyLevel::query()
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->where('min_points', '<=', $pointsToTest)
            ->orderByDesc('min_points')
            ->first();

        $this->assertEquals('Silver', $level->name);
    }

    public function test_level_max_points(): void
    {
        $level = LoyaltyLevel::query()->create([
            'name' => 'Bronze',
            'min_points' => 0,
            'max_points' => 499,
            'earning_rate' => 1.0,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 0,
        ]);

        $this->assertEquals(0, $level->min_points);
        $this->assertEquals(499, $level->max_points);
    }

    public function test_level_without_max_points(): void
    {
        $level = LoyaltyLevel::query()->create([
            'name' => 'Platinum',
            'min_points' => 5000,
            'max_points' => null,
            'earning_rate' => 1.5,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 3,
        ]);

        $this->assertNull($level->max_points);
    }
}
