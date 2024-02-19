
import { Injectable } from '@nestjs/common';
import { Repository } from 'typeorm';
import { RoosterEntity } from '../entities/Rooster.entity';
import { RoosterViewEntity } from '../entities/RoosterView.entity';
import { InjectRepository } from '@nestjs/typeorm';
import { IHeliosService } from '../../../core/base/IHelios.service';
import { AuditEntity } from '../../../core/entities/Audit.entity';

@Injectable()
export class RoosterService extends IHeliosService<RoosterEntity, RoosterEntity> {
  constructor(
    @InjectRepository(RoosterEntity) protected readonly repository: Repository<RoosterEntity>,
    @InjectRepository(RoosterViewEntity) protected readonly viewRepository: Repository<RoosterViewEntity>,
    @InjectRepository(AuditEntity) protected readonly auditRepository: Repository<AuditEntity>
  ) {
    super(repository, viewRepository, auditRepository);
  }
}