import { GetObjectsFilterDTO } from '../../../core/base/GetObjectsFilterDTO';
import { CompetentiesEntity } from '../entities/Competenties.entity';

import { IsOptional, IsString } from 'class-validator';
import { FindOptionsOrder, In } from 'typeorm';

export class CompetentiesGetObjectsFilterDTO extends GetObjectsFilterDTO<CompetentiesEntity> {
  @IsString()
  @IsOptional()
  LEERFASE_ID?: string;

  get defaultGetObjectsSortering(): FindOptionsOrder<CompetentiesEntity> {
    return { LEERFASE_ID: 'ASC', BLOK_ID: 'ASC', VOLGORDE: 'ASC', ID: 'ASC' };
  }

  bouwGetObjectsFindOptions() {
    super.bouwGetObjectsFindOptions();

    if (this.LEERFASE_ID) {
      this.findOptionsBuilder.and({ LEERFASE_ID: In(this.LEERFASE_ID.split(',')) });
    }

    this.findOptionsBuilder.relations({ leerfase: true });
  }
}
