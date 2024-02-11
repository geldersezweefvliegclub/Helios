import { GetObjectsFilterDTO } from '../../../core/base/GetObjectsFilterDTO';
import { LedenEntity } from '../entities/Leden.entity';
import { IsOptional, IsString } from 'class-validator';
import { Between, FindOptionsOrder, In, Like } from 'typeorm';

export class LedenGetObjectsFilterDTO extends GetObjectsFilterDTO<LedenEntity> {
  @IsOptional()
  @IsString()
  SELECTIE?: string;

  @IsOptional()
  @IsString()
  IN?: string;

  @IsOptional()
  @IsString()
  TYPES?: string;

  @IsOptional()
  @IsString()
  CLUBLEDEN?: boolean;

  @IsOptional()
  @IsString()
  INSTRUCTEURS?: boolean;

  @IsOptional()
  @IsString()
  DDWV_CREW?: boolean;

  @IsOptional()
  @IsString()
  LIERISTEN?: boolean;

  @IsOptional()
  @IsString()
  LIO?: boolean;

  @IsOptional()
  @IsString()
  STARTLEIDERS?: boolean;

  get defaultGetObjectsSortering(): FindOptionsOrder<LedenEntity> {
    return {
      ACHTERNAAM: 'ASC',
      VOORNAAM: 'ASC',
    };
  }

  bouwGetObjectsFindOptions(): void {
    super.bouwGetObjectsFindOptions();

    if (this.IN) {
      // TODO: Het Leden filter in PHP implementeert ID als een IN filter op IN. Dit is niet hetzelfde als de vliegtuigen implementatie.
      // Ook is de IN filter niet geimplementeerd in de PHP versie.
      // Ik implementeer hier IN wel, en ID zoals bij vliegtuigen. Check of dit klopt.
      this.findOptionsBuilder.and({ ID: In(this.IN.split(',').map((id) => parseInt(id))) });
    }

    if (this.TYPES) {
      // todo beperk query mogelijkheden als gebruiker DDWVer is
      this.findOptionsBuilder.and({ LIDTYPE_ID: In(this.TYPES.split(',').map((id) => parseInt(id))) });
    }

    if (this.CLUBLEDEN) {
      this.findOptionsBuilder.and({ LIDTYPE_ID: Between(601, 606) });
    }

    if (this.INSTRUCTEURS) {
      this.findOptionsBuilder.and({ INSTRUCTEUR: this.INSTRUCTEURS });
    }

    if (this.DDWV_CREW) {
      this.findOptionsBuilder.and({ DDWV_CREW: this.DDWV_CREW });
    }

    if (this.LIERISTEN) {
      this.findOptionsBuilder.and({ LIERIST: this.LIERISTEN });
    }

    if (this.LIO) {
      this.findOptionsBuilder.and({ LIERIST_LIO: this.LIO });
    }

    if (this.STARTLEIDERS) {
      this.findOptionsBuilder.and({ STARTLEIDER: this.STARTLEIDERS });
    }

    // Moet als laatste komen zodat alle andere (simpele) AND filters al zijn toegevoegd
    if (this.SELECTIE) {
      const currentWhere = this.findOptionsBuilder.takeWhereCondition(0);
      this.findOptionsBuilder.clearWhere();
      const findOperator = Like(`%${this.SELECTIE}%`);
      this.findOptionsBuilder.or({ NAAM: findOperator, ...currentWhere });
      this.findOptionsBuilder.or({ TELEFOON: findOperator, ...currentWhere });
      this.findOptionsBuilder.or({ MOBIEL: findOperator, ...currentWhere });
      this.findOptionsBuilder.or({ NOODNUMMER: findOperator, ...currentWhere });
      this.findOptionsBuilder.or({ EMAIL: findOperator, ...currentWhere });
    }

    // Load zusterclub relation here instead of using eager: true. Eager: true create a maximum callstack error
    this.findOptionsBuilder.relations({ ZUSTERCLUB: true, BUDDY: true, BUDDY2: true});
  }
}
