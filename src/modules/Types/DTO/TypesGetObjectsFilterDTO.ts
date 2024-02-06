import { IsBoolean, IsDate, IsInt, IsNumber, IsOptional, IsString } from 'class-validator';
import { GetObjectsFilterDTO } from '../../../core/DTO/GetObjectsFilterDTO';
import { Transform } from 'class-transformer';
import { TypeEntity } from '../entities/Type.entity';
import { FindManyOptions } from 'typeorm';
import { isFindOptionsWhereAnObject } from '../../../core/helpers/functions';

export class TypesGetObjectsFilterDTO extends GetObjectsFilterDTO<TypeEntity> {
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

  buildTypeORMFindManyObject(): FindManyOptions<TypeEntity> {
    const findOptions = super.buildTypeORMFindManyObject();

    if(this.GROEP && isFindOptionsWhereAnObject(findOptions.where)) {
      findOptions.where.GROEP = this.GROEP;
    }

    if (this.CODE && isFindOptionsWhereAnObject(findOptions.where)) {
      findOptions.where.CODE = this.CODE;
    }

    if (this.EXT_REF && isFindOptionsWhereAnObject(findOptions.where)) {
      findOptions.where.EXT_REF = this.EXT_REF;
    }

    if (this.OMSCHRIJVING && isFindOptionsWhereAnObject(findOptions.where)) {
      findOptions.where.OMSCHRIJVING = this.OMSCHRIJVING;
    }

    if (this.SORTEER_VOLGORDE && isFindOptionsWhereAnObject(findOptions.where)) {
      findOptions.where.SORTEER_VOLGORDE = this.SORTEER_VOLGORDE;
    }

    if (this.READ_ONLY && isFindOptionsWhereAnObject(findOptions.where)) {
      findOptions.where.READ_ONLY = this.READ_ONLY;
    }

    if (this.BEDRAG && isFindOptionsWhereAnObject(findOptions.where)) {
      findOptions.where.BEDRAG = this.BEDRAG;
    }

    if (this.EENHEDEN && isFindOptionsWhereAnObject(findOptions.where)) {
      findOptions.where.EENHEDEN = this.EENHEDEN;
    }

    return findOptions;
  }
}
