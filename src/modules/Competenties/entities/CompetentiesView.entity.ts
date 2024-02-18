import {IHeliosDatabaseEntity} from '../../../core/base/IHeliosDatabaseEntity';
import {ViewColumn, ViewEntity} from 'typeorm';
import {booleanTransformer} from "../../../core/helpers/transformers/BooleanTransformer";

@ViewEntity({
  name: 'competenties_view',
expression: `SELECT
    c.*,
    t.OMSCHRIJVING AS LEERFASE
FROM
    ref_competenties c
    LEFT JOIN ref_types t ON (c.LEERFASE_ID = t.ID)
WHERE
    c.VERWIJDERD = 0
ORDER BY
    LEERFASE_ID, BLOK_ID, VOLGORDE, ID`})
export class CompetentiesViewEntity extends IHeliosDatabaseEntity{
  @ViewColumn()
  public ID: number;

    @ViewColumn()
    public VOLGORDE: number;

    @ViewColumn()
    public LEERFASE_ID: number;

    @ViewColumn()
    public BLOK_ID: number;

    @ViewColumn()
    public BLOK: number;

    @ViewColumn()
    public ONDERWERP: string | null;

    @ViewColumn()
    public DOCUMENTATIE: string | null;

    @ViewColumn({transformer: booleanTransformer})
    public GELDIGHEID: boolean;

    @ViewColumn({transformer: booleanTransformer})
    public SCORE: boolean;

    @ViewColumn({transformer: booleanTransformer})
    public VERWIJDERD: boolean;

    @ViewColumn()
    public LAATSTE_AANPASSING: Date;

    @ViewColumn()
    public LEERFASE: string | null;
}
