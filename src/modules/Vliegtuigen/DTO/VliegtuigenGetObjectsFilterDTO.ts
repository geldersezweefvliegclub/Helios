import { IsInt, IsOptional, IsString, IsBoolean } from 'class-validator';
import { GetObjectsFilterDTO } from '../../../core/DTO/GetObjectsFilterDTO';
import { Transform } from 'class-transformer';
import { VliegtuigenEntity } from '../entities/Vliegtuigen.entity';
import { FindManyOptions, In } from 'typeorm';
import { isFindOptionsWhereAnObject } from '../../../core/helpers/functions';

export class VliegtuigenGetObjectsFilterDTO extends GetObjectsFilterDTO<VliegtuigenEntity> {
  @IsInt()
  @IsOptional()
  @Transform((params) => params.value == null ? null : parseInt(params.value))
  ZITPLAATSEN?: number;

  @IsString()
  @IsOptional()
  SELECTIE?: string;

  @IsString()
  @IsOptional()
  IN?: string;

  @IsString()
  @IsOptional()
  TYPES?: string;

  @IsBoolean()
  @IsOptional()
  @Transform((params) => params.value === 'true')
  SLEEPKIST?: boolean;

  @IsBoolean()
  @IsOptional()
  @Transform((params) => params.value === 'true')
  ZELFSTART?: boolean;

  @IsBoolean()
  @IsOptional()
  @Transform((params) => params.value === 'true')
  TMG?: boolean;

  bouwGetObjectsFindOptions(): FindManyOptions<VliegtuigenEntity> {
    const findOptions = super.bouwGetObjectsFindOptions();

    if(this.ZITPLAATSEN && isFindOptionsWhereAnObject(findOptions.where)) {
      findOptions.where.ZITPLAATSEN = this.ZITPLAATSEN;
    }

    // todo: search for either REGISTRATIE, CALLSIGN or FLARM_CODE
    if(this.SELECTIE && isFindOptionsWhereAnObject(findOptions.where)) {
      console.warn('SELECTIE is not implemented yet');
    }

    if(this.IN && isFindOptionsWhereAnObject(findOptions.where)) {
      findOptions.where.ID = In(this.IN.split(',').map((id) => parseInt(id)));
    }

    if(this.TYPES && isFindOptionsWhereAnObject(findOptions.where)) {
      findOptions.where.TYPE_ID = In(this.TYPES.split(',').map((id) => parseInt(id)));
    }

    if(this.SLEEPKIST && isFindOptionsWhereAnObject(findOptions.where)) {
      findOptions.where.SLEEPKIST = this.SLEEPKIST;
    }

    if(this.ZELFSTART && isFindOptionsWhereAnObject(findOptions.where)) {
      findOptions.where.ZELFSTART = this.ZELFSTART;
    }

    if(this.TMG && isFindOptionsWhereAnObject(findOptions.where)) {
      findOptions.where.TMG = this.TMG;
    }

    return findOptions;
  }
}
