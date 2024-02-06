import { FindManyOptions, FindOptionsOrder } from 'typeorm';
import { IHeliosEntity } from './IHeliosEntity';
import { IsBoolean, IsDate, IsInt, IsOptional } from 'class-validator';
import { isFindOptionsWhereAnObject } from '../helpers/functions';
import { Transform } from 'class-transformer';

export abstract class IHeliosFilterDTO<Entity extends IHeliosEntity> {
  @IsInt()
  @IsOptional()
  ID?: number;

  @IsOptional()
  @IsBoolean()
  @Transform((params) => params.value === 'true')
  VERWIJDERD?: boolean;

  @IsDate()
  @IsOptional()
  @Transform((params) => new Date(params.value))
  LAATSTE_AANPASSING?: Date;


  /**
   * Bouw een FindManyOptions object op die gebruikt kan worden door TypeORM om objecten op te halen.
   * Het object wordt opgebouwd op basis van de properties van de DTO.
   * Override deze methode in een subclass om extra properties toe te voegen.
   */
  bouwGetObjectsFindOptions(): FindManyOptions<Entity> {
    // Gebruik de object variant, niet de array variant.
    // De array variant is voor OR queries, de object variant is voor AND queries, wat we willen.
    const findOptions: FindManyOptions<Entity> = {
      where: {
        // Default VERWIJDERD naar false
        VERWIJDERD: false as never
      },
      order: this.defaultGetObjectsSortering,
    };

    if (this.ID && isFindOptionsWhereAnObject(findOptions.where)) {
      findOptions.where.ID = this.ID as never;
    }

    if (this.VERWIJDERD && isFindOptionsWhereAnObject(findOptions.where)) {
      findOptions.where.VERWIJDERD = this.VERWIJDERD as never;
    }

    if (this.LAATSTE_AANPASSING && isFindOptionsWhereAnObject(findOptions.where)) {
      findOptions.where.LAATSTE_AANPASSING = this.LAATSTE_AANPASSING as never;
    }

    return findOptions;
  }

  /**
   * De default sortering die gebruikt wordt voor GetObjects, als er verder in de DTO geen sortering is opgegeven.
   */
  abstract get defaultGetObjectsSortering(): FindOptionsOrder<Entity>;

  /**
   * Zet de sortering om naar een FindOptionsOrder object
   * Input: SORT=CLUBKIST DESC, VOLGORDE, REGISTRATIE
   * Output: { CLUBKIST: 'DESC', VOLGORDE: 'ASC', REGISTRATIE: 'ASC' }
   * @param commaSeparatedString
   * @private
   */
  protected bouwSorteringOp(commaSeparatedString: string): FindOptionsOrder<Entity> {
    const order: Record<string, string> = {};

    const sortFields = commaSeparatedString.split(',');

    for (const sortField of sortFields) {
      const parts = sortField.trim().split(' ');
      const field = parts[0];
      // Pak de de waarde van de sortering, als die er niet is, dan default naar ASC
      order[field as keyof typeof order] = parts.length > 1 ? parts[1] : 'ASC';
    }

    return order as FindOptionsOrder<Entity>;
  }
}
