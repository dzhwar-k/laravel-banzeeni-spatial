<?php

declare(strict_types=1);

namespace MatanYadaev\EloquentSpatial;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use MatanYadaev\EloquentSpatial\Objects\Geometry;

class GeometryCast implements CastsAttributes
{
  /** @var class-string<Geometry> */
  private string $className;

  /**
   * @param  class-string<Geometry>  $className
   */
  public function __construct(string $className)
  {
    $this->className = $className;
  }

  /**
   * @param  Model  $model
   * @param  string  $key
   * @param  string|Expression|null  $value
   * @param  array<string, mixed>  $attributes
   * @return Geometry|null
   */
  public function get($model, string $key, $value, array $attributes): ?Geometry
  {
    if (! $value) {
      return null;
    }

    if ($value instanceof Expression) {
      $wkt = $this->extractWktFromExpression($value);
      $srid = $this->extractSridFromExpression($value);

      return $this->className::fromWkt($wkt, $srid);
    }

    return $this->className::fromWkb($value);
  }

  /**
   * @param  Model  $model
   * @param  string  $key
   * @param  Geometry|mixed|null  $value
   * @param  array<string, mixed>  $attributes
   * @return Expression|null
   *
   * @throws InvalidArgumentException
   */
  public function set($model, string $key, $value, array $attributes): Expression|null
  {
    if (! $value) {
      return null;
    }

    if (! ($value instanceof $this->className)) {
      $geometryType = is_object($value) ? $value::class : gettype($value);
      throw new InvalidArgumentException(
        sprintf('Expected %s, %s given.', static::class, $geometryType)
      );
    }

    $wkt = $value->toWkt();

    $isMariaDb = $model->getConnection()->isMaria();

    if ($isMariaDb) {
      // @codeCoverageIgnoreStart
      return DB::raw("ST_GeomFromText('{$wkt}', {$value->srid})");
      // @codeCoverageIgnoreEnd
    }

    return DB::raw("ST_GeomFromText('{$wkt}', {$value->srid}, 'axis-order=long-lat')");
  }

  private function extractWktFromExpression(Expression $expression): string
  {
    preg_match('/ST_GeomFromText\(\'(.+)\', .+(, .+)?\)/', (string) $expression, $match);

    return $match[1];
  }

  private function extractSridFromExpression(Expression $expression): int
  {
    preg_match('/ST_GeomFromText\(\'.+\', (.+)(, .+)?\)/', (string) $expression, $match);

    return (int) $match[1];
  }
}
