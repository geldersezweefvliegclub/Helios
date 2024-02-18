import {IHeliosDatabaseEntity} from '../../../core/base/IHeliosDatabaseEntity';
import {ViewColumn, ViewEntity} from 'typeorm';
import {booleanTransformer} from "../../../core/helpers/transformers/BooleanTransformer";

@ViewEntity({
  name: 'progressie_view',
    expression: `
SELECT
    p.*,
    t.OMSCHRIJVING AS LEERFASE,
    c.ONDERWERP AS COMPETENTIE,
    l.NAAM AS LID_NAAM,
    i.NAAM AS INSTRUCTEUR_NAAM
FROM
    oper_progressie p
        LEFT JOIN ref_competenties c ON (p.COMPETENTIE_ID = c.ID)
        LEFT JOIN ref_leden l ON (p.LID_ID = l.ID)
        LEFT JOIN ref_leden i ON (p.INSTRUCTEUR_ID = i.ID)
        LEFT JOIN ref_types t ON (c.LEERFASE_ID = t.ID)
WHERE
    p.VERWIJDERD = 0
ORDER BY
    LID_ID, LAATSTE_AANPASSING DESC, c.ID`
})
export class ProgressieViewEntity extends IHeliosDatabaseEntity {
  @ViewColumn()
  ID: number;

  @ViewColumn()
  LID_ID: number;

  @ViewColumn()
  COMPETENTIE_ID: number;

  @ViewColumn()
  INSTRUCTEUR_ID: number;

  @ViewColumn()
  OPMERKINGEN: string | null;

  @ViewColumn()
  INGEVOERD: Date;

  @ViewColumn()
  LINK_ID: number;

  @ViewColumn()
  GELDIG_TOT: Date;

  @ViewColumn()
  SCORE: number;

  @ViewColumn({transformer: booleanTransformer})
  VERWIJDERD: boolean;

  @ViewColumn()
  LAATSTE_AANPASSING: Date;

  @ViewColumn()
  LEERFASE: string | null;


  @ViewColumn()
  COMPETENTIE: string | null;

  @ViewColumn()
  LID_NAAM: string | null;

  @ViewColumn()
  INSTRUCTEUR_NAAM: string | null;
}
