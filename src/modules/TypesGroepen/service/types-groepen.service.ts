import { Injectable } from '@nestjs/common';
import { Repository } from 'typeorm';
import { TypeGroepEntity } from '../entities/TypeGroep.entity';
import { InjectRepository } from '@nestjs/typeorm';
import { TypesGroepenGetObjectsFilterDTO } from '../DTO/TypesGroepenGetObjectsFilterDTO';
import { IHeliosService } from '../../../core/base/IHelios.service';

@Injectable()
export class TypesGroepenService extends IHeliosService<TypeGroepEntity, TypesGroepenGetObjectsFilterDTO> {
  constructor(@InjectRepository(TypeGroepEntity) protected readonly repository: Repository<TypeGroepEntity>) {
    super(repository);
  }
}
