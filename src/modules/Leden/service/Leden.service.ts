
import { Injectable } from '@nestjs/common';
import { Repository } from 'typeorm';
import { LedenEntity } from '../entities/Leden.entity';
import { InjectRepository } from '@nestjs/typeorm';
import { IHeliosService } from '../../../core/base/IHelios.service';

@Injectable()
export class LedenService extends IHeliosService<LedenEntity> {
  constructor(@InjectRepository(LedenEntity) protected readonly repository: Repository<LedenEntity>) {
    super(repository);
  }
}