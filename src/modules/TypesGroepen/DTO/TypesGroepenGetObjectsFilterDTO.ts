import { GetObjectsFilterDTO } from '../../../core/base/GetObjectsFilterDTO';
import { IsBoolean, IsOptional, IsString } from 'class-validator';
import { TypeGroepEntity } from '../entities/TypeGroep.entity';

export class TypesGroepenGetObjectsFilterDTO extends GetObjectsFilterDTO<TypeGroepEntity> {
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

  @IsBoolean()
  @IsOptional()
  READ_ONLY?: boolean;

  @IsBoolean()
  @IsOptional()
  BEDRAG_EENHEDEN: boolean;

  bouwGetObjectsFindOptions(): void {
    super.bouwGetObjectsFindOptions();

    if (this.CODE) {
      this.findOptionsBuilder.and({ CODE: this.CODE });
    }

    if (this.EXT_REF) {
      this.findOptionsBuilder.and({ EXT_REF: this.EXT_REF });
    }

    if (this.OMSCHRIJVING) {
      this.findOptionsBuilder.and({ OMSCHRIJVING: this.OMSCHRIJVING });
    }

    if (this.SORTEER_VOLGORDE) {
      this.findOptionsBuilder.and({ SORTEER_VOLGORDE: this.SORTEER_VOLGORDE });
    }

    if (this.READ_ONLY) {
      this.findOptionsBuilder.and({ READ_ONLY: this.READ_ONLY });
    }

    if (this.BEDRAG_EENHEDEN) {
      this.findOptionsBuilder.and({ BEDRAG_EENHEDEN: this.BEDRAG_EENHEDEN });
    }
  }
}
