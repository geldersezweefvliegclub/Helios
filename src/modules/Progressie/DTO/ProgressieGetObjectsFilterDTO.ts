import { GetObjectsFilterDTO } from '../../../core/base/GetObjectsFilterDTO';
import { ProgressieEntity } from '../entities/Progressie.entity';
import { IsNumber, IsOptional, IsString } from 'class-validator';
import { Transform } from 'class-transformer';
import {FindOptionsOrder} from "typeorm";

export class ProgressieGetObjectsFilterDTO extends GetObjectsFilterDTO<ProgressieEntity> {
  @IsOptional()
  @IsNumber()
  @Transform((params) => params.value == null ? null : parseInt(params.value))
  LID_ID?: number;

  @IsOptional()
  @IsNumber()
  @Transform((params) => params.value == null ? null : parseInt(params.value))
  COMPETENTIE_ID?: number;

  @IsOptional()
  @IsNumber()
  @Transform((params) => params.value == null ? null : parseInt(params.value))
  INSTRUCTEUR_ID?: number;

  @IsOptional()
  @IsNumber()
  @Transform((params) => params.value == null ? null : parseInt(params.value))
  LINK_ID?: number;

  @IsOptional()
  @IsString()
  @Transform((params) => params.value == null ? null : params.value)
  GELDIG_TOT?: Date;

  @IsOptional()
  @IsNumber()
  @Transform((params) => params.value == null ? null : parseInt(params.value))
  SCORE?: number;

  bouwGetObjectsFindOptions() {
    super.bouwGetObjectsFindOptions();

    if (this.LID_ID) {
      this.findOptionsBuilder.and({ LID_ID: this.LID_ID });
    }

    if (this.COMPETENTIE_ID) {
      this.findOptionsBuilder.and({ COMPETENTIE_ID: this.COMPETENTIE_ID });
    }

    if (this.INSTRUCTEUR_ID) {
      this.findOptionsBuilder.and({ INSTRUCTEUR_ID: this.INSTRUCTEUR_ID });
    }

    if (this.LINK_ID) {
      this.findOptionsBuilder.and({ LINK_ID: this.LINK_ID });
    }

    if (this.GELDIG_TOT) {
      this.findOptionsBuilder.and({ GELDIG_TOT: this.GELDIG_TOT });
    }

    if (this.SCORE) {
      this.findOptionsBuilder.and({ SCORE: this.SCORE });
    }

    this.findOptionsBuilder.relations({LidEntity: true, InstructeurEntity: true, CompetentieEntity: true});
  }

  get defaultGetObjectsSortering(): FindOptionsOrder<ProgressieEntity> {
    return {LID_ID: 'ASC', LAATSTE_AANPASSING: 'DESC', ID: 'ASC'};
  }
}
