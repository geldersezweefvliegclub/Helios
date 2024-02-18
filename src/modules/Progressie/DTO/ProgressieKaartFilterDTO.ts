import {IsDate, IsNumber, IsOptional, IsString} from 'class-validator';
import {Transform} from 'class-transformer';
import {FindOptionsBuilder} from "../../../core/services/filter-builder/find-options-builder.service";
import {ProgressieViewEntity} from "../entities/ProgressieView.entity";

export class ProgressieKaartFilterDTO {
  public readonly findOptionsBuilder = new FindOptionsBuilder<ProgressieViewEntity>();

  @IsOptional()
  @IsNumber()
  @Transform((params) => params.value == null ? null : parseInt(params.value))
  LID_ID?: number;

  @IsOptional()
  @IsString()
  VELDEN?: string;

  @IsDate()
  @IsOptional()
  @Transform((params) => new Date(params.value))
  LAATSTE_AANPASSING?: Date;

  bouwFindOptions() {
    if (this.LID_ID) {
        this.findOptionsBuilder.and({LID_ID: this.LID_ID});
    }

    if (this.VELDEN){
        this.findOptionsBuilder.select(this.VELDEN);
    }

    if (this.LAATSTE_AANPASSING) {
        this.findOptionsBuilder.and({LAATSTE_AANPASSING: this.LAATSTE_AANPASSING});
    }
  }
}
