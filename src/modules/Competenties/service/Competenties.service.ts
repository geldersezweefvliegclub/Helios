
import { Injectable } from '@nestjs/common';
import { Repository } from 'typeorm';
import { CompetentiesEntity } from '../entities/Competenties.entity';
import { InjectRepository } from '@nestjs/typeorm';
import { IHeliosService } from '../../../core/base/IHelios.service';
import { AuditEntity } from '../../../core/entities/Audit.entity';

@Injectable()
export class CompetentiesService extends IHeliosService<CompetentiesEntity> {
  constructor(
    @InjectRepository(CompetentiesEntity) protected readonly repository: Repository<CompetentiesEntity>,
    @InjectRepository(AuditEntity) protected readonly auditRepository: Repository<AuditEntity>
  ) {
    super(repository, auditRepository);
  }
}