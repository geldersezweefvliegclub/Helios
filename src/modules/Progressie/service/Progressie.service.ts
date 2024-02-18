import {Injectable} from '@nestjs/common';
import {Repository} from 'typeorm';
import {ProgressieEntity} from '../entities/Progressie.entity';
import {InjectRepository} from '@nestjs/typeorm';
import {IHeliosService} from '../../../core/base/IHelios.service';
import {AuditEntity} from '../../../core/entities/Audit.entity';
import {ProgressieKaartFilterDTO} from "../DTO/ProgressieKaartFilterDTO";
import {CompetentiesEntity} from "../../Competenties/entities/Competenties.entity";
import {GetObjectsResponse} from "../../../core/base/GetObjectsResponse";
import {ProgressiekaartDTO} from "../DTO/ProgressiekaartDTO";
import {ProgressieViewEntity} from '../entities/ProgressieView.entity';

@Injectable()
export class ProgressieService extends IHeliosService<ProgressieEntity, ProgressieViewEntity> {
  constructor(
    @InjectRepository(ProgressieEntity) protected readonly repository: Repository<ProgressieEntity>,
    @InjectRepository(ProgressieViewEntity) protected readonly viewRepository: Repository<ProgressieViewEntity>,
    @InjectRepository(CompetentiesEntity) protected readonly competentiesRepository: Repository<CompetentiesEntity>,
    @InjectRepository(AuditEntity) protected readonly auditRepository: Repository<AuditEntity>
  ) {
    super(repository, viewRepository, auditRepository);
  }

    async getProgressiekaart(filter: ProgressieKaartFilterDTO): Promise<GetObjectsResponse<ProgressiekaartDTO>> {
        const datasetRaw = await this.competentiesRepository.createQueryBuilder('c')
            .select('c.BLOK as BLOK, c.BLOK_ID as BLOK_ID, c.DOCUMENTATIE as DOCUMENTATIE, p.GELDIG_TOT as GELDIG_TOT, c.ID as ID, p.INGEVOERD as INGEVOERD, p.INSTRUCTEUR_NAAM as INSTRUCTEUR_NAAM, p.LEERFASE as LEERFASE, c.LEERFASE_ID as LEERFASE_ID, c.ONDERWERP as ONDERWERP, p.OPMERKINGEN as OPMERKINGEN, p.ID as PROGRESSIE_ID, c.SCORE as SCORE')
            .leftJoin(qb => {
                return qb
                    .select('*')
                    .from(ProgressieViewEntity, 'p')
                    .where('p.LID_ID = :lidId', { lidId: filter.LID_ID });
            }, 'p', 'c.ID = p.COMPETENTIE_ID')
            .orderBy({
                'c.LEERFASE_ID': 'ASC',
                'c.VOLGORDE': 'ASC',
                'c.ID': 'ASC',
            })
            .getRawMany();
        // todo fix ugly casting
        return this.bouwDatasetResponse(datasetRaw, filter.findOptionsBuilder.findOptions) as unknown as GetObjectsResponse<ProgressiekaartDTO>;
    }
}