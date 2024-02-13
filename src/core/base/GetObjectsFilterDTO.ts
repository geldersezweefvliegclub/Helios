import { IsNumber, IsOptional, IsString } from 'class-validator';
import { Transform } from 'class-transformer';
import { IHeliosFilterDTO } from './IHeliosFilterDTO';
import { FindOptionsOrder } from 'typeorm';
import { IHeliosObject } from './IHeliosObject';

/**
 * Een generieke DTO gebruikt door `GetObjects` endpoints zodat NestJS de query parameters kan valideren en parsen.
 * Deze properties kunnen voor elk willekeurig object die `GetObjects` implementeert worden gebruikt.
 * Voeg extra properties toe aan deze DTO door een subclass te maken van deze DTO en die te gebruiken in de `GetObjects` endpoint.
 * Hier kun je ook de defaultSortOrder en bouwSorteringOp methodes uitbreiden / overschrijven.
 */
export abstract class GetObjectsFilterDTO<Entity extends IHeliosObject> extends IHeliosFilterDTO<Entity> {
  @IsOptional()
  @IsNumber()
  @Transform((params) => params.value == null ? null : parseInt(params.value))
  MAX?: number;

  @IsOptional()
  @IsNumber()
  START?: number;

  @IsOptional()
  @IsString()
  VELDEN?: string;

  @IsOptional()
  @IsString()
  SORT?: string;

  bouwGetObjectsFindOptions(): void {
    super.bouwGetObjectsFindOptions()

    if (this.SORT) {
      this.findOptionsBuilder.order(this.SORT);
    }

    if (this.MAX) {
      this.findOptionsBuilder.max(this.MAX);
    }

    if (this.START) {
      this.findOptionsBuilder.skip(this.START);
    }

    if (this.VELDEN) {
      // WORKAROUND: TypeORM throws invalid SQL error when using `select`, `order` and `take` together, in particular when ordering on a field not included in the select.
      // Therefore we always add the fields we also apply default sorting on to the `select` clause.
      // GH Issue: https://github.com/typeorm/typeorm/issues/9719
      // GH Minimal reproduce repo: https://github.com/Staijn1/typeorm-relation-ordering
      const defaultSortering = this.defaultGetObjectsSortering;
      const select: Record<string, boolean> = {};
      for (const field in defaultSortering) {
        select[field] = true;
      }
      this.findOptionsBuilder.select(this.VELDEN, select as any);
    }
  }

  get defaultGetObjectsSortering(): FindOptionsOrder<Entity> {
    return {} as FindOptionsOrder<Entity>;
  }
}
