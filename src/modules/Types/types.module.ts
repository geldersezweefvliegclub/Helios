import { Module } from '@nestjs/common';
import { TypesController } from './controller/types.controller';
import { TypesService } from './service/types.service';

@Module({
  controllers: [TypesController],
  providers: [TypesService]
})
export class TypesModule {}
