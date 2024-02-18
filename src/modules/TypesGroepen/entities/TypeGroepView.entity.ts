import {ViewColumn, ViewEntity} from 'typeorm';
import {IHeliosDatabaseEntity} from '../../../core/base/IHeliosDatabaseEntity';
import {booleanTransformer} from "../../../core/helpers/transformers/BooleanTransformer";

@ViewEntity({
    name: 'types_groepen_view',
    expression: `SELECT groepen.*
                 FROM ref_types_groepen groepen
                 WHERE groepen.VERWIJDERD = 0
                 ORDER BY SORTEER_VOLGORDE, ID;`
})
export class TypeGroepViewEntity extends IHeliosDatabaseEntity {
    @ViewColumn()
    public ID: number;

    @ViewColumn()
    public CODE: string | null;

    @ViewColumn()
    public EXT_REF: string | null;

    @ViewColumn()
    public OMSCHRIJVING: string | null;

    @ViewColumn()
    public SORTEER_VOLGORDE: number;

    @ViewColumn({transformer: booleanTransformer})
    public READ_ONLY: boolean;

    @ViewColumn({transformer: booleanTransformer})
    public BEDRAG_EENHEDEN: boolean;

    @ViewColumn({transformer: booleanTransformer})
    public VERWIJDERD: boolean;

    @ViewColumn()
    public LAATSTE_AANPASSING: Date;
}
