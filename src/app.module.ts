import { MiddlewareConsumer, Module, NestModule } from '@nestjs/common';
import { ConfigModule } from '@nestjs/config';
import { TypeOrmModule } from '@nestjs/typeorm';
import { TypeOrmConfigService } from './typeorm/typeorm.service';
import { TypesModule } from './modules/Types/types.module';
import { TypesGroepenModule } from './modules/TypesGroepen/types-groepen.module';
import { VliegtuigenModule } from './modules/Vliegtuigen/vliegtuigen.module';
import { LedenModule } from './modules/Leden/Leden.module';
import { RequestLoggingMiddleware } from './core/middleware/request-logging/request-logging.middleware';
import { CompetentiesModule } from './modules/Competenties/Competenties.module';
import { ProgressieModule } from './modules/Progressie/Progressie.module';
import { RoosterModule } from './modules/Rooster/Rooster.module';
import { DienstenModule } from './modules/Diensten/Diensten.module';
import {ConfigurationUtils} from "./configuration/ConfigurationUtils";

@Module({
  imports: [
    ConfigModule.forRoot({
      load: [ConfigurationUtils.LoadConfiguration],
      isGlobal: true,
      cache: true,
      validate: ConfigurationUtils.ValidateConfiguration,
    }),
    TypeOrmModule.forRootAsync({ useClass: TypeOrmConfigService}),
    TypesModule,
    TypesGroepenModule,
    VliegtuigenModule,
    LedenModule,
    CompetentiesModule,
    ProgressieModule,
    RoosterModule,
    DienstenModule
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
    // Add request logging for all registered routes
    consumer.apply(RequestLoggingMiddleware).forRoutes('*');
  }
}
