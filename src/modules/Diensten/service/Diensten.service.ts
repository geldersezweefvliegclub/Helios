
import { Injectable } from '@nestjs/common';
import { Repository } from 'typeorm';
import { DienstenEntity } from '../entities/Diensten.entity';
import { DienstenViewEntity } from '../entities/DienstenView.entity';
import { InjectRepository } from '@nestjs/typeorm';
import { IHeliosService } from '../../../core/base/IHelios.service';
import { AuditEntity } from '../../../core/entities/Audit.entity';

@Injectable()
export class DienstenService extends IHeliosService<DienstenEntity, DienstenEntity> {
  constructor(
    @InjectRepository(DienstenEntity) protected readonly repository: Repository<DienstenEntity>,
    @InjectRepository(DienstenViewEntity) protected readonly viewRepository: Repository<DienstenViewEntity>,
    @InjectRepository(AuditEntity) protected readonly auditRepository: Repository<AuditEntity>
  ) {
    super(repository, viewRepository, auditRepository);
  }
}