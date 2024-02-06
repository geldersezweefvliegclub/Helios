import { IsNumber, IsOptional, IsString } from 'class-validator';
import { Transform } from 'class-transformer';
import { IHeliosFilterDTO } from './IHeliosFilterDTO';
import { FindManyOptions, FindOptionsOrder, FindOptionsSelect } from 'typeorm';
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

  buildTypeORMFindManyObject(): FindManyOptions<Entity> {
    const findOptions = super.buildTypeORMFindManyObject()
    let order: FindOptionsOrder<Entity> = this.defaultSortOrder;

    if (this.SORT) {
      order = this.bouwSorteringOp(this.SORT);
    }

    if (this.MAX) {
      findOptions.take = this.MAX;
    }

    if (this.START) {
      findOptions.skip = this.START;
    }

    if (this.VELDEN) {
      const select: Record<string, boolean> = {};
      // VELDEN is een comma separated string met de velden die je wilt selecteren.
      // TypeORM wil graag een object met de velden die je wilt selecteren, waarbij de waarde true is.
      // Bijvoorbeeld: { ID: true, OMSCHRIJVING: true }

      const velden = this.VELDEN.split(',');
      velden.forEach((veld) => {
        select[veld.trim()] = true;
      });
      findOptions.select = select as FindOptionsSelect<Entity>;
    }

    findOptions.order = order;
    return findOptions;
  }

  get defaultSortOrder(): FindOptionsOrder<Entity> {
    return {} as FindOptionsOrder<Entity>;
  }
}
