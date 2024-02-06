import { Column, Entity, PrimaryGeneratedColumn } from 'typeorm';

@Entity('ref_types_groepen')
export class TypeGroepEntity {

    @PrimaryGeneratedColumn()
    ID: number;

    @Column({ type: 'varchar', length: 10, nullable: true })
    CODE: string;

    @Column({ type: 'varchar', length: 25, nullable: true })
    EXT_REF: string;

    @Column({ type: 'varchar', length: 75 })
    OMSCHRIJVING: string;

    @Column({ type: 'tinyint', nullable: true })
    SORTEER_VOLGORDE: number;

    @Column({ type: 'tinyint', default: 0 })
    READ_ONLY: number;

    @Column({ type: 'tinyint', default: 0 })
    BEDRAG_EENHEDEN: number;

    @Column("boolean", { default: false, name: "VERWIJDERD" })
    VERWIJDERD: boolean;

    @Column({ type: 'timestamp', default: () => 'CURRENT_TIMESTAMP', onUpdate: 'CURRENT_TIMESTAMP' })
    LAATSTE_AANPASSING: Date;
}
