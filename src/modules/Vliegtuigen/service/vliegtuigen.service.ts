import { Injectable } from '@nestjs/common';
import { Repository } from 'typeorm';
import { InjectRepository } from '@nestjs/typeorm';
import { IHeliosService } from '../../../core/base/IHelios.service';
import { VliegtuigenEntity } from '../entities/Vliegtuigen.entity';
import { AuditEntity } from '../../../core/entities/Audit.entity';
import {VliegtuigenViewEntity} from "../entities/VliegtuigenView.entity";

@Injectable()
export class VliegtuigenService extends IHeliosService<VliegtuigenEntity, VliegtuigenViewEntity> {
  constructor(@InjectRepository(VliegtuigenEntity) protected readonly repository: Repository<VliegtuigenEntity>,
              @InjectRepository(VliegtuigenViewEntity) protected readonly viewRepository: Repository<VliegtuigenViewEntity>,
              protected readonly auditRepository: Repository<AuditEntity>
  ) {
    super(repository, viewRepository, auditRepository);
  }
}
