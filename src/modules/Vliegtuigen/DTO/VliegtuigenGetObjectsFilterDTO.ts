import { IsBoolean, IsInt, IsOptional, IsString } from 'class-validator';
import { GetObjectsFilterDTO } from '../../../core/base/GetObjectsFilterDTO';
import { Transform } from 'class-transformer';
import { FindOptionsOrder, In, Like } from 'typeorm';
import {VliegtuigenViewEntity} from "../entities/VliegtuigenView.entity";

export class VliegtuigenGetObjectsFilterDTO extends GetObjectsFilterDTO<VliegtuigenViewEntity> {
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
  CLUBKIST?: boolean;

  @IsBoolean()
  @IsOptional()
  @Transform((params) => params.value === 'true')
  TMG?: boolean;

  bouwGetObjectsFindOptions(): void {
    super.bouwGetObjectsFindOptions();

    if (this.ZITPLAATSEN) {
      this.findOptionsBuilder.and({ ZITPLAATSEN: this.ZITPLAATSEN });
    }


    if (this.IN) {
      this.findOptionsBuilder.and({ ID: In(this.IN.split(',').map((id) => parseInt(id))) });
    }

    if (this.TYPES) {
      this.findOptionsBuilder.and({ TYPE_ID: In(this.TYPES.split(',').map((id) => parseInt(id))) });
    }

    if (this.SLEEPKIST) {
      this.findOptionsBuilder.and({ SLEEPKIST: this.SLEEPKIST });
    }

    if (this.ZELFSTART) {
      this.findOptionsBuilder.and({ ZELFSTART: this.ZELFSTART });
    }

    if (this.CLUBKIST) {
      this.findOptionsBuilder.and({ CLUBKIST: this.CLUBKIST });
    }

    if (this.TMG) {
      this.findOptionsBuilder.and({ TMG: this.TMG });
    }

    // Moet als laatste komen zodat alle andere (simpele) AND filters al zijn toegevoegd
    if (this.SELECTIE) {
      const currentWhere = this.findOptionsBuilder.takeWhereCondition(0);
      this.findOptionsBuilder.clearWhere();
      const findOperator = Like(`%${this.SELECTIE}%`);
      this.findOptionsBuilder.or({ REGISTRATIE: findOperator, ...currentWhere });
      this.findOptionsBuilder.or({ CALLSIGN: findOperator, ...currentWhere });
      this.findOptionsBuilder.or({ FLARMCODE: findOperator, ...currentWhere });
    }
  }

  get defaultGetObjectsSortering(): FindOptionsOrder<VliegtuigenViewEntity> {
    return {
      CLUBKIST: 'DESC',
      VOLGORDE: 'ASC',
      REGISTRATIE: 'ASC',
    }
  }
}
