import { GetObjectsFilterDTO } from '../../../core/base/GetObjectsFilterDTO';
import { DienstenViewEntity } from '../entities/DienstenView.entity';
import { IsBoolean, IsDate, IsInt, IsOptional, IsString } from 'class-validator';
import { In, MoreThanOrEqual } from 'typeorm';

export class DienstenGetObjectsFilterDTO extends GetObjectsFilterDTO<DienstenViewEntity> {
  @IsOptional()
  @IsInt()
  LID_ID?: number;

  @IsOptional()
  @IsDate()
  DATUM?: Date;

  @IsOptional()
  @IsDate()
  BEGIN_DATUM?: Date;

  @IsOptional()
  @IsDate()
  EIND_DATUM?: Date;

  @IsOptional()
  @IsString()
  TYPES?: string;

  @IsOptional()
  @IsBoolean()
  AANWEZIG?: boolean;

  @IsOptional()
  @IsBoolean()
  AFWEZIG?: boolean;

  bouwGetObjectsFindOptions() {
    super.bouwGetObjectsFindOptions();

    if( this.LID_ID) {
      this.findOptionsBuilder.and({ LID_ID: this.LID_ID })
    }

    if( this.DATUM) {
      this.findOptionsBuilder.and({ DATUM: this.DATUM })
    }

    if( this.BEGIN_DATUM) {
      this.findOptionsBuilder.and({ DATUM: MoreThanOrEqual(this.BEGIN_DATUM) })
    }

    if( this.EIND_DATUM) {
      this.findOptionsBuilder.and({ DATUM: MoreThanOrEqual(this.EIND_DATUM) })
    }

    if( this.TYPES) {
      this.findOptionsBuilder.and({ TYPE_DIENST_ID: In(this.TYPES.split(',').map((id) => parseInt(id))) });
    }

    if( this.AANWEZIG) {
      this.findOptionsBuilder.and({ AANWEZIG: this.AANWEZIG });
    }

    if( this.AFWEZIG) {
      this.findOptionsBuilder.and({ AFWEZIG: this.AFWEZIG });
    }
  }
}
