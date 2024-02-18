import { IsInt, IsNumber, IsOptional, IsString } from 'class-validator';
import { GetObjectsFilterDTO } from '../../../core/base/GetObjectsFilterDTO';
import { Transform } from 'class-transformer';
import { TypeEntity } from '../entities/Type.entity';
import { FindOptionsOrder } from 'typeorm';
import { TypeViewEntity } from "../entities/TypeView.entity";

export class TypesGetObjectsFilterDTO extends GetObjectsFilterDTO<TypeViewEntity> {
  @IsInt()
  @IsOptional()
  @Transform((params) => params.value == null ? null : parseInt(params.value))
  GROEP?: number;

  @IsString()
  @IsOptional()
  CODE?: string | null;

  @IsString()
  @IsOptional()
  EXT_REF?: string | null;

  @IsString()
  @IsOptional()
  OMSCHRIJVING?: string;

  @IsString()
  @IsOptional()
  @Transform((params) => params.value == null ? null : parseInt(params.value))
  SORTEER_VOLGORDE?: number | null;

  @IsString()
  @IsOptional()
  @Transform((params) => params.value === 'true')
  READ_ONLY?: boolean;

  @IsNumber()
  @IsOptional()
  @Transform((params) => params.value == null ? null : parseInt(params.value))
  BEDRAG?: number | null;

  @IsNumber()
  @IsOptional()
  @Transform((params) => params.value == null ? null : parseInt(params.value))
  EENHEDEN?: number | null;

  get defaultGetObjectsSortering(): FindOptionsOrder<TypeEntity> {
    return {
      GROEP: 'ASC',
      SORTEER_VOLGORDE: 'ASC',
      ID: 'ASC',
    }
  }

  bouwGetObjectsFindOptions(): void {
    super.bouwGetObjectsFindOptions();

    if (this.GROEP) {
      this.findOptionsBuilder.and({ GROEP: this.GROEP });
    }

    if (this.CODE) {
      this.findOptionsBuilder.and({ CODE: this.CODE });
    }

    if (this.EXT_REF) {
      this.findOptionsBuilder.and({ EXT_REF: this.EXT_REF });
    }

    if (this.OMSCHRIJVING) {
      this.findOptionsBuilder.and({ OMSCHRIJVING: this.OMSCHRIJVING });
    }

    if (this.SORTEER_VOLGORDE) {
      this.findOptionsBuilder.and({ SORTEER_VOLGORDE: this.SORTEER_VOLGORDE });
    }

    if (this.READ_ONLY) {
      this.findOptionsBuilder.and({ READ_ONLY: this.READ_ONLY });
    }

    if (this.BEDRAG) {
      this.findOptionsBuilder.and({ BEDRAG: this.BEDRAG });
    }

    if (this.EENHEDEN) {
      this.findOptionsBuilder.and({ EENHEDEN: this.EENHEDEN });
    }
  }
}
