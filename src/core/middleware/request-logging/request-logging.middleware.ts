import { Injectable, Logger, NestMiddleware } from '@nestjs/common';
import { Request, Response } from 'express';

@Injectable()
export class RequestLoggingMiddleware implements NestMiddleware {
  private readonly logger = new Logger(RequestLoggingMiddleware.name);
  /**
   * When a request comes in, log the endpoint which was called
   * @param req
   * @param res
   * @param next
   */
  use(req: Request, res: Response, next: () => void) {
    this.logger.log(`Starting request for: ${req.method} ${req.originalUrl}`);
    next();
  }
}
