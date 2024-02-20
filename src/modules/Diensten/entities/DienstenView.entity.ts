import { IHeliosDatabaseEntity } from '../../../core/base/IHeliosDatabaseEntity';
import { ViewColumn, ViewEntity } from 'typeorm';
import { booleanTransformer } from '../../../core/helpers/transformers/BooleanTransformer';

@ViewEntity({
  name: 'diensten_view',
  expression: `SELECT
    d.*,
    l.NAAM AS NAAM,
    i.NAAM AS INGEVOERD_DOOR,
    t.OMSCHRIJVING AS TYPE_DIENST
FROM
    oper_diensten d
        LEFT JOIN ref_types t ON (d.TYPE_DIENST_ID = t.ID)
        LEFT JOIN ref_leden l ON (d.LID_ID = l.ID)
        LEFT JOIN ref_leden i ON (d.INGEVOERD_DOOR_ID = i.ID)
WHERE
    d.VERWIJDERD = 0
ORDER BY
    DATUM, SORTEER_VOLGORDE`})
export class DienstenViewEntity extends IHeliosDatabaseEntity{
  @ViewColumn()
  public ID: number;

  @ViewColumn()
  public DATUM: Date;

  @ViewColumn()
  public ROOSTER_ID: number;

  @ViewColumn()
  public LID_ID: number;

  @ViewColumn()
  public TYPE_DIENST_ID: number;

  @ViewColumn()
  public INGEVOERD_DOOR_ID: number;

  @ViewColumn({transformer: booleanTransformer})
  public AANWEZIG: boolean;

  @ViewColumn({transformer: booleanTransformer})
  public UITBETAALD: boolean;

  @ViewColumn({transformer: booleanTransformer})
  public AFWEZIG: boolean;

  @ViewColumn()
  public NAAM: string;

  @ViewColumn()
  public INGEVOERD_DOOR: string;

  @ViewColumn()
  public TYPE_DIENST: string;
}
