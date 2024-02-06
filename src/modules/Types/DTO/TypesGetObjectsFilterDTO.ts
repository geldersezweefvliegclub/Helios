import { IsBoolean, IsDate, IsInt, IsNumber, IsOptional, IsString } from 'class-validator';
import { GetObjectsFilterDTO } from '../../../core/DTO/GetObjectsFilterDTO';
import { Transform } from 'class-transformer';
import { TypeEntity } from '../entities/Type.entity';

export class TypesGetObjectsFilterDTO extends GetObjectsFilterDTO<TypeEntity> {
  @IsInt()
  @IsOptional()
  @Transform((params) => params.value == null ? null : parseInt(params.value))
  ID?: number;

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

  @IsOptional()
  @IsBoolean()
  @Transform((params) => params.value === 'true')
  VERWIJDERD?: boolean;

  @IsDate()
  @IsOptional()
  @Transform((params) => new Date(params.value))
  LAATSTE_AANPASSING?: Date;

  // todo: override defaultSortOrder and buildTypeORMFindManyObject om nieuwe properties van deze DTO te gebruiken
}
