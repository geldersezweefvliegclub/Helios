import { GetObjectsFilterDTO } from '../../../core/DTO/GetObjectsFilterDTO';
import { IsBoolean, IsDate, IsInt, IsNumber, IsOptional, IsString } from 'class-validator';
import { Transform } from 'class-transformer';
import { TypeGroepEntity } from '../entities/TypeGroep.entity';

export class TypesGroepenGetObjectsFilterDTO extends GetObjectsFilterDTO<TypeGroepEntity> {
  @IsInt()
  @IsOptional()
  ID?: number;

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
  READ_ONLY?: number;

  @IsNumber()
  @IsOptional()
  BEDRAG_EENHEDEN: number;

  @IsOptional()
  @IsBoolean()
  @Transform((params) => params.value === 'true')
  VERWIJDERD?: boolean;

  @IsDate()
  @IsOptional()
  @Transform((params) => new Date(params.value))
  LAATSTE_AANPASSING?: Date;

  // todo: override defaultSorting en buildTypeORMFindManyObject om de properties van deze class toe te voegen
}
