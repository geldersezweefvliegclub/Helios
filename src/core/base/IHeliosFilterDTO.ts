import { FindOptionsOrder } from 'typeorm';
import { IHeliosEntity } from './IHeliosEntity';
import { IsBoolean, IsDate, IsInt, IsOptional } from 'class-validator';
import { Transform } from 'class-transformer';
import { FindOptionsBuilder } from '../services/filter-builder/find-options-builder.service';

export abstract class IHeliosFilterDTO<Entity extends IHeliosEntity> {
  public findOptionsBuilder = new FindOptionsBuilder<Entity>({
    where: {
      // Default VERWIJDERD naar false
      VERWIJDERD: false as never
    },
  });

  @IsInt()
  @IsOptional()
  @Transform((params) => params.value == null ? null : parseInt(params.value))
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
  bouwGetObjectsFindOptions(): void {
    this.findOptionsBuilder.findOptions.order = this.defaultGetObjectsSortering;

    if (this.ID) {
      this.findOptionsBuilder.and({ ID: this.ID as never});
    }

    if (this.VERWIJDERD) {
      this.findOptionsBuilder.and({ VERWIJDERD: this.VERWIJDERD as never });
    }

    if (this.LAATSTE_AANPASSING) {
      this.findOptionsBuilder.and({ LAATSTE_AANPASSING: this.LAATSTE_AANPASSING as never });
    }
  }

  /**
   * De default sortering die gebruikt wordt voor GetObjects, als er verder in de DTO geen sortering is opgegeven.
   */
  abstract get defaultGetObjectsSortering(): FindOptionsOrder<Entity>;
}
