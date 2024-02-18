import {ViewColumn, ViewEntity} from 'typeorm';
import {IHeliosDatabaseEntity} from '../../../core/base/IHeliosDatabaseEntity';
import {booleanTransformer} from "../../../core/helpers/transformers/BooleanTransformer";

@ViewEntity({
  name: 'vliegtuigen_view',
  expression: `SELECT
    v.*,
    CONCAT(IFNULL(v.REGISTRATIE,''),' (',IFNULL(v.CALLSIGN,''),')') AS REG_CALL,
    t.OMSCHRIJVING AS VLIEGTUIGTYPE,
    lkl.ONDERWERP AS BEVOEGDHEID_LOKAAL,
    ovl.ONDERWERP AS BEVOEGDHEID_OVERLAND
FROM
    ref_vliegtuigen v
        LEFT JOIN ref_types t ON (v.TYPE_ID = t.ID)
        LEFT JOIN ref_competenties lkl ON (v.BEVOEGDHEID_LOKAAL_ID = lkl.ID)
        LEFT JOIN ref_competenties ovl ON (v.BEVOEGDHEID_OVERLAND_ID = ovl.ID)
WHERE
    v.VERWIJDERD = 0
ORDER BY
    CLUBKIST DESC, VOLGORDE, REGISTRATIE;
`})
export class VliegtuigenViewEntity extends IHeliosDatabaseEntity{
  @ViewColumn()
  ID: number;

  @ViewColumn()
  REGISTRATIE: string;

  @ViewColumn()
  CALLSIGN: string;

  @ViewColumn()
  ZITPLAATSEN: number;

  @ViewColumn({transformer: booleanTransformer})
  CLUBKIST: boolean;

  @ViewColumn()
  FLARMCODE: string;

  @ViewColumn()
  TYPE_ID: number;

  @ViewColumn({transformer: booleanTransformer})
  TMG: boolean;

  @ViewColumn({transformer: booleanTransformer})
  ZELFSTART: boolean;

  @ViewColumn({transformer: booleanTransformer})
  SLEEPKIST: boolean;

  @ViewColumn()
  VOLGORDE: number;

  @ViewColumn({transformer: booleanTransformer})
  INZETBAAR: boolean;

  @ViewColumn()
  TRAINER: boolean;

  @ViewColumn()
  URL: string;

  @ViewColumn()
  BEVOEGDHEID_LOKAAL_ID: number;

  @ViewColumn()
  BEVOEGDHEID_OVERLAND_ID: number;

  @ViewColumn()
  OPMERKINGEN: string;

  @ViewColumn({transformer: booleanTransformer})
  VERWIJDERD: boolean;

  @ViewColumn()
  LAATSTE_AANPASSING: Date;

  @ViewColumn()
  REG_CALL: string;

  @ViewColumn()
  VLIEGTUIGTYPE: string;

  @ViewColumn()
  BEVOEGDHEID_LOKAAL: string;

  @ViewColumn()
  BEVOEGDHEID_OVERLAND: string;
}
