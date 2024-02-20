import { IHeliosDatabaseEntity } from '../../../core/base/IHeliosDatabaseEntity';
import { ViewColumn, ViewEntity } from 'typeorm';
import { booleanTransformer } from '../../../core/helpers/transformers/BooleanTransformer';

@ViewEntity({
  name: 'rooster_view',
  expression: `SELECT rooster.*
                              FROM oper_rooster AS rooster
                              WHERE rooster.VERWIJDERD = 0
                              ORDER BY DATUM`,
})
export class RoosterViewEntity extends IHeliosDatabaseEntity {
  @ViewColumn()
  public DATUM: Date;

  @ViewColumn({ transformer: booleanTransformer })
  public DDWV: boolean;

  @ViewColumn({ transformer: booleanTransformer })
  public CLUB_BEDRIJF: boolean;

  @ViewColumn()
  public MIN_SLEEPSTART: number;

  @ViewColumn()
  public MIN_LIERSTART: number;

  @ViewColumn()
  public OPMERKINGEN: string;
}
