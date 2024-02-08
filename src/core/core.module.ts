import { Module } from '@nestjs/common';
import { FindOptionsBuilder } from './services/filter-builder/find-options-builder.service';

@Module({
  providers: [FindOptionsBuilder]
})
export class CoreModule {}
