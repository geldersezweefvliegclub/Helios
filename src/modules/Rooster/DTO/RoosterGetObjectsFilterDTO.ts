import { GetObjectsFilterDTO } from '../../../core/base/GetObjectsFilterDTO';
import { RoosterViewEntity } from '../entities/RoosterView.entity';
import { LessThanOrEqual, MoreThanOrEqual } from 'typeorm';
import { IsDate, IsOptional } from 'class-validator';

export class RoosterGetObjectsFilterDTO extends GetObjectsFilterDTO<RoosterViewEntity> {
  @IsOptional()
  @IsDate()
  DATUM?: Date;

  @IsOptional()
  @IsDate()
  BEGIN_DATUM?: Date;

  @IsOptional()
  @IsDate()
  EIND_DATUM?: Date;

  bouwGetObjectsFindOptions() {
    super.bouwGetObjectsFindOptions();

    if (this.DATUM) {
      this.findOptionsBuilder.and({ DATUM: this.DATUM });
    }

    if (this.BEGIN_DATUM) {
      this.findOptionsBuilder.and({ DATUM: MoreThanOrEqual(this.BEGIN_DATUM) });
    }

    if (this.EIND_DATUM) {
      this.findOptionsBuilder.and({ DATUM: LessThanOrEqual(this.EIND_DATUM) });
    }
  }
}
