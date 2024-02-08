import { IsBoolean, IsInt, IsOptional, IsString } from 'class-validator';
import { GetObjectsFilterDTO } from '../../../core/base/GetObjectsFilterDTO';
import { Transform } from 'class-transformer';
import { VliegtuigenEntity } from '../entities/Vliegtuigen.entity';
import { In } from 'typeorm';

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

    // todo: search for either REGISTRATIE, CALLSIGN or FLARM_CODE
    if (this.SELECTIE) {
      console.warn('SELECTIE is not implemented yet');
    }

    if (this.IN) {
      this.findOptionsBuilder.and({ ID: In(this.IN.split(',').map((id) => parseInt(id)) ) });
    }

    if (this.TYPES) {
      this.findOptionsBuilder.and({ TYPE_ID: In(this.TYPES.split(',').map((id) => parseInt(id)) ) });
    }

    if(this.SLEEPKIST) {
      this.findOptionsBuilder.and({ SLEEPKIST: this.SLEEPKIST });
    }

    if(this.ZELFSTART) {
      this.findOptionsBuilder.and({ ZELFSTART: this.ZELFSTART });
    }

    if(this.CLUBKIST) {
      this.findOptionsBuilder.and({ CLUBKIST: this.CLUBKIST });
    }

    if(this.TMG) {
      this.findOptionsBuilder.and({ TMG: this.TMG });
    }
  }
}
