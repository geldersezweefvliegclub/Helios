import { IsNumber, IsOptional, IsString } from 'class-validator';
import { Transform } from 'class-transformer';
import { IHeliosFilterDTO } from './IHeliosFilterDTO';
import { FindOptionsOrder } from 'typeorm';
import { IHeliosEntity } from './IHeliosEntity';

/**
 * Een generieke DTO gebruikt door `GetObjects` endpoints zodat NestJS de query parameters kan valideren en parsen.
 * Deze properties kunnen voor elk willekeurig object die `GetObjects` implementeert worden gebruikt.
 * Voeg extra properties toe aan deze DTO door een subclass te maken van deze DTO en die te gebruiken in de `GetObjects` endpoint.
 * Hier kun je ook de defaultSortOrder en bouwSorteringOp methodes uitbreiden / overschrijven.
 */
export abstract class GetObjectsFilterDTO<Entity extends IHeliosEntity> extends IHeliosFilterDTO<Entity> {
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
      this.findOptionsBuilder.order({});
    }

    if (this.MAX) {
      this.findOptionsBuilder.max(this.MAX);
    }

    if (this.START) {
      this.findOptionsBuilder.skip(this.START);
    }

    if (this.VELDEN) {
      this.findOptionsBuilder.select(this.VELDEN);
    }
  }

  get defaultGetObjectsSortering(): FindOptionsOrder<Entity> {
    return {} as FindOptionsOrder<Entity>;
  }
}
