import {GetObjectsFilterDTO} from '../../../core/base/GetObjectsFilterDTO';

import {IsOptional, IsString} from 'class-validator';
import {In} from 'typeorm';
import {CompetentiesViewEntity} from "../entities/CompetentiesView.entity";

export class CompetentiesGetObjectsFilterDTO extends GetObjectsFilterDTO<CompetentiesViewEntity> {
  @IsString()
  @IsOptional()
  LEERFASE_ID?: string;

  bouwGetObjectsFindOptions() {
    super.bouwGetObjectsFindOptions();

    if (this.LEERFASE_ID) {
      this.findOptionsBuilder.and({ LEERFASE_ID: In(this.LEERFASE_ID.split(',')) });
    }
  }
}
