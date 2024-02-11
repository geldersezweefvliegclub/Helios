import { Injectable } from '@nestjs/common';
import { Repository } from 'typeorm';
import { InjectRepository } from '@nestjs/typeorm';
import { IHeliosService } from '../../../core/base/IHelios.service';
import { VliegtuigenEntity } from '../entities/Vliegtuigen.entity';
import { AuditEntity } from '../../../core/entities/Audit.entity';

@Injectable()
export class VliegtuigenService extends IHeliosService<VliegtuigenEntity> {
  constructor(@InjectRepository(VliegtuigenEntity) protected readonly repository: Repository<VliegtuigenEntity>,
              protected readonly auditRepository: Repository<AuditEntity>
  ) {
    super(repository, auditRepository);
  }
}
