import { Compiler, WebpackError } from 'webpack';

export interface FriendlyError {
  /** The error message */
  message: string;
  /** Original file path where the error occurred */
  file?: string;
  /** Original webpack error object */
  webpackError?: WebpackError;
  /** Error name/title */
  name?: string;
  /** Error type identifier (e.g., 'module-not-found', 'lint-error') */
  type?: string;
  /** Error severity level (higher = more severe) */
  severity?: number;
  /** Original stack trace */
  originalStack?: Array<{
    fileName?: string;
    lineNumber?: number;
    columnNumber?: number;
    functionName?: string;
  }>;
  /** Module name for module-not-found errors */
  module?: string;
  [key: string]: unknown;
}

export type Severity = 'error' | 'warn';

export type LogLevel = 'INFO' | 'WARNING' | 'ERROR' | 'SILENT' | 'info' | 'warning' | 'error' | 'silent';

export type Transformer = (error: FriendlyError) => FriendlyError;

export type Formatter = (errors: FriendlyError[], severity: Severity) => string[];

export interface Reporter {
  /** Log a success message */
  success(tag: string, message: string): void;
  /** Log an info message */
  info(tag: string, message?: string): void;
  /** Log a note message */
  note(message: string): void;
  /** Log a warning message */
  warn(tag: string, ...messages: string[]): void;
  /** Log an error message */
  error(tag: string, ...messages: string[]): void;
  /** Log a generic message */
  log(...messages: string[]): void;
  /** Clear the console */
  clearConsole(): void;
}

export interface CompilationSuccessInfo {
  /** Messages to display after successful compilation */
  messages?: string[];
  /** Additional notes to display after successful compilation */
  notes?: string[];
}

export interface FriendlyErrorsWebpackPluginOptions {
  /** Information to display on successful compilation */
  compilationSuccessInfo?: CompilationSuccessInfo;
  /**
   * Callback function called when errors occur
   * @param severity - The severity level ('error' or 'warn')
   * @param errors - Array of processed errors
   */
  onErrors?: (severity: Severity, errors: FriendlyError[]) => void;
  /**
   * Whether to clear the console between compilations
   * @default true
   */
  clearConsole?: boolean;
  /**
   * Log level for output
   * - INFO: all logs
   * - WARNING: warnings and errors
   * - ERROR: only errors
   * - SILENT: no log
   * @default 'INFO'
   */
  logLevel?: LogLevel;
  /**
   * Reporter to use for output
   * Can be 'base', 'consola', an npm package name, or a Reporter instance
   * @default 'base'
   */
  reporter?: 'base' | 'consola' | string | Reporter;
  /** Additional formatters to apply to errors */
  additionalFormatters?: Formatter[];
  /** Additional transformers to apply to errors */
  additionalTransformers?: Transformer[];
}

declare class FriendlyErrorsWebpackPlugin {
  constructor(options?: FriendlyErrorsWebpackPluginOptions);
  apply(compiler: Compiler): void;
}

export default FriendlyErrorsWebpackPlugin;
export = FriendlyErrorsWebpackPlugin;
