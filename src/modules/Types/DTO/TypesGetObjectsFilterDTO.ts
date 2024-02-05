import { IsBoolean, IsDate, IsInt, IsNumber, IsOptional, IsString } from 'class-validator';
import { GetObjectsFilterDTO } from '../../../core/DTO/GetObjectsFilterDTO';
import { Transform } from 'class-transformer';

export class TypesGetObjectsFilterDTO extends GetObjectsFilterDTO{
  @IsInt()
  @IsOptional()
  ID?: number;

  @IsInt()
  @IsOptional()
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
  SORTEER_VOLGORDE?: number | null;

  @IsString()
  @IsOptional()
  @Transform((params) => params.value === 'true')
  READ_ONLY?: boolean;

  @IsNumber()
  @IsOptional()
  BEDRAG?: number | null;

  @IsNumber()
  @IsOptional()
  EENHEDEN?: number | null;

  @IsOptional()
  @IsBoolean()
  @Transform((params) => params.value === 'true')
  VERWIJDERD?: boolean;

  @IsDate()
  @IsOptional()
  @Transform((params) => new Date(params.value))
  LAATSTE_AANPASSING?: Date;
}
