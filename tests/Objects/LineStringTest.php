<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Tests\TestModels\TestPlace;

uses(DatabaseMigrations::class);

it('creates a model record with line string', function (): void {
  $lineString = new LineString([
    new Point(0, 180),
    new Point(1, 179),
  ]);

  /** @var TestPlace $testPlace */
  $testPlace = TestPlace::factory()->create(['line_string' => $lineString]);

  expect($testPlace->line_string)->toBeInstanceOf(LineString::class);
  expect($testPlace->line_string)->toEqual($lineString);
});

it('creates a model record with line string with SRID', function (): void {
  $lineString = new LineString([
    new Point(0, 180),
    new Point(1, 179),
  ], 4326);

  /** @var TestPlace $testPlace */
  $testPlace = TestPlace::factory()->create(['line_string' => $lineString]);

  expect($testPlace->line_string->srid)->toBe(4326);
});

it('creates line string from JSON', function (): void {
  $lineString = new LineString([
    new Point(0, 180),
    new Point(1, 179),
  ]);

  $lineStringFromJson = LineString::fromJson('{"type":"LineString","coordinates":[[180,0],[179,1]]}');

  expect($lineStringFromJson)->toEqual($lineString);
});

it('creates line string with SRID from JSON', function (): void {
  $lineString = new LineString([
    new Point(0, 180),
    new Point(1, 179),
  ], 4326);

  $lineStringFromJson = LineString::fromJson('{"type":"LineString","coordinates":[[180,0],[179,1]]}', 4326);

  expect($lineStringFromJson)->toEqual($lineString);
});

it('generates line string JSON', function (): void {
  $lineString = new LineString([
    new Point(0, 180),
    new Point(1, 179),
  ]);

  $json = $lineString->toJson();

  $expectedJson = '{"type":"LineString","coordinates":[[180,0],[179,1]]}';
  expect($json)->toBe($expectedJson);
});

it('generates line string feature collection JSON', function (): void {
  $lineString = new LineString([
    new Point(0, 180),
    new Point(1, 179),
  ]);

  $featureCollectionJson = $lineString->toFeatureCollectionJson();

  $expectedFeatureCollectionJson = '{"type":"FeatureCollection","features":[{"type":"Feature","properties":[],"geometry":{"type":"LineString","coordinates":[[180,0],[179,1]]}}]}';
  expect($featureCollectionJson)->toBe($expectedFeatureCollectionJson);
});

it('creates line string from WKT', function (): void {
  $lineString = new LineString([
    new Point(0, 180),
    new Point(1, 179),
  ]);

  $lineStringFromWkt = LineString::fromWkt('LINESTRING(180 0, 179 1)');

  expect($lineStringFromWkt)->toEqual($lineString);
});

it('creates line string with SRID from WKT', function (): void {
  $lineString = new LineString([
    new Point(0, 180),
    new Point(1, 179),
  ], 4326);

  $lineStringFromWkt = LineString::fromWkt('LINESTRING(180 0, 179 1)', 4326);

  expect($lineStringFromWkt)->toEqual($lineString);
});

it('generates line string WKT', function (): void {
  $lineString = new LineString([
    new Point(0, 180),
    new Point(1, 179),
  ]);

  $wkt = $lineString->toWkt();

  $expectedWkt = 'LINESTRING(180 0, 179 1)';
  expect($wkt)->toBe($expectedWkt);
});

it('creates line string from WKB', function (): void {
  $lineString = new LineString([
    new Point(0, 180),
    new Point(1, 179),
  ]);

  $lineStringFromWkb = LineString::fromWkb($lineString->toWkb());

  expect($lineStringFromWkb)->toEqual($lineString);
});

it('creates line string with SRID from WKB', function (): void {
  $lineString = new LineString([
    new Point(0, 180),
    new Point(1, 179),
  ], 4326);

  $lineStringFromWkb = LineString::fromWkb($lineString->toWkb());

  expect($lineStringFromWkb)->toEqual($lineString);
});

it('throws exception when line string has less than two points', function (): void {
  expect(function (): void {
    new LineString([
      new Point(0, 180),
    ]);
  })->toThrow(InvalidArgumentException::class);
});

it('throws exception when creating line string from incorrect geometry', function (): void {
  expect(function (): void {
    // @phpstan-ignore-next-line
    new LineString([
      Polygon::fromJson('{"type":"Polygon","coordinates":[[[180,0],[179,1],[178,2],[177,3],[180,0]]]}'),
    ]);
  })->toThrow(InvalidArgumentException::class);
});
