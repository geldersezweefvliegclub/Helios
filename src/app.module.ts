import { MiddlewareConsumer, Module, NestModule } from '@nestjs/common';
import { ConfigModule } from '@nestjs/config';
import { TypeOrmModule } from '@nestjs/typeorm';
import { TypeOrmConfigService } from './typeorm/typeorm.service';
import { TypesModule } from './modules/Types/types.module';
import { TypesGroepenModule } from './modules/TypesGroepen/types-groepen.module';
import { VliegtuigenModule } from './modules/Vliegtuigen/vliegtuigen.module';
import { LedenModule } from './modules/Leden/Leden.module';
import { RequestLoggingMiddleware } from './core/middleware/request-logging/request-logging.middleware';

@Module({
  imports: [
    ConfigModule.forRoot({ isGlobal: true }),
    TypeOrmModule.forRootAsync({ useClass: TypeOrmConfigService}),
    TypesModule,
    TypesGroepenModule,
    VliegtuigenModule,
    LedenModule,
  ],
  controllers: [],
  providers: [TypeOrmConfigService],
})
export class AppModule implements NestModule {
  /**
   * Configure middlewares
   * @param consumer
   */
  configure(consumer: MiddlewareConsumer) {
    consumer.apply(RequestLoggingMiddleware).forRoutes('*');
  }
}
